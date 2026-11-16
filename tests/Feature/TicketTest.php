<?php

namespace Tests\Feature;

use App\Enums\EstadoTicket;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    private User $tecnico;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tecnico = User::factory()->create();
        Sanctum::actingAs($this->tecnico);
    }

    public function test_crea_un_ticket_abierto_con_el_usuario_como_autor(): void
    {
        $cliente = Cliente::factory()->create();

        $this->postJson('/api/tickets', [
            'titulo' => 'No funciona la impresora de tickets',
            'descripcion' => 'Desde esta mañana la impresora no responde.',
            'prioridad' => 'alta',
            'cliente_id' => $cliente->id,
        ])
            ->assertCreated()
            ->assertJsonPath('data.estado', 'abierto')
            ->assertJsonPath('data.autor.id', $this->tecnico->id)
            ->assertJsonPath('data.cliente.id', $cliente->id);
    }

    public function test_valida_los_campos_obligatorios(): void
    {
        $this->postJson('/api/tickets', ['prioridad' => 'imposible'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titulo', 'descripcion', 'prioridad', 'cliente_id']);
    }

    public function test_filtra_por_estado_y_busca_por_texto(): void
    {
        Ticket::factory()->create(['titulo' => 'Error en facturación electrónica']);
        Ticket::factory()->enEstado(EstadoTicket::Resuelto)->create(['titulo' => 'Error en facturación antigua']);
        Ticket::factory()->create(['titulo' => 'Cambio de contraseña']);

        $this->getJson('/api/tickets?estado=abierto&buscar=facturación')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.titulo', 'Error en facturación electrónica');
    }

    public function test_filtra_los_tickets_sin_asignar(): void
    {
        Ticket::factory()->create(['tecnico_id' => $this->tecnico->id]);
        Ticket::factory()->count(2)->create();

        $this->getJson('/api/tickets?tecnico_id=sin_asignar')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_al_resolver_un_ticket_guarda_la_fecha_de_resolucion(): void
    {
        $ticket = Ticket::factory()->create();

        $this->patchJson("/api/tickets/{$ticket->id}", ['estado' => 'resuelto'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'resuelto');

        $this->assertNotNull($ticket->fresh()->resuelto_at);
    }

    public function test_reabrir_un_ticket_borra_la_fecha_de_resolucion(): void
    {
        $ticket = Ticket::factory()->enEstado(EstadoTicket::Resuelto)->create();

        $this->patchJson("/api/tickets/{$ticket->id}", ['estado' => 'en_progreso'])->assertOk();

        $this->assertNull($ticket->fresh()->resuelto_at);
    }

    public function test_no_se_puede_cerrar_un_ticket_sin_resolverlo_antes(): void
    {
        $ticket = Ticket::factory()->create();

        $this->patchJson("/api/tickets/{$ticket->id}", ['estado' => 'cerrado'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_un_ticket_cerrado_no_se_puede_modificar_ni_comentar(): void
    {
        $ticket = Ticket::factory()->enEstado(EstadoTicket::Cerrado)->create();

        $this->patchJson("/api/tickets/{$ticket->id}", ['prioridad' => 'baja'])
            ->assertUnprocessable();

        $this->postJson("/api/tickets/{$ticket->id}/comentarios", ['texto' => 'Hola'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('texto');
    }

    public function test_comentar_un_ticket_abierto_lo_pasa_a_en_progreso(): void
    {
        $ticket = Ticket::factory()->create();

        $this->postJson("/api/tickets/{$ticket->id}/comentarios", ['texto' => 'Lo reviso ahora mismo'])
            ->assertCreated()
            ->assertJsonPath('data.autor.id', $this->tecnico->id);

        $this->assertSame(EstadoTicket::EnProgreso, $ticket->fresh()->estado);
    }

    public function test_solo_un_admin_puede_borrar_tickets(): void
    {
        $ticket = Ticket::factory()->create();

        $this->deleteJson("/api/tickets/{$ticket->id}")->assertForbidden();

        Sanctum::actingAs(User::factory()->admin()->create());
        $this->deleteJson("/api/tickets/{$ticket->id}")->assertNoContent();

        $this->assertModelMissing($ticket);
    }
}
