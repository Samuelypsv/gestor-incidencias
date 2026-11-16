<?php

namespace Tests\Feature;

use App\Enums\EstadoTicket;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EstadisticasTest extends TestCase
{
    use RefreshDatabase;

    public function test_devuelve_los_contadores_del_panel(): void
    {
        Sanctum::actingAs(User::factory()->create());

        Ticket::factory()->count(2)->create(['prioridad' => 'urgente']);
        Ticket::factory()->enEstado(EstadoTicket::Cerrado)->create([
            'created_at' => now()->subHours(5),
            'resuelto_at' => now(),
        ]);

        $this->getJson('/api/estadisticas')
            ->assertOk()
            ->assertJsonPath('por_estado.abierto', 2)
            ->assertJsonPath('por_estado.cerrado', 1)
            ->assertJsonPath('pendientes_por_prioridad.urgente', 2)
            ->assertJsonPath('sin_asignar', 2)
            ->assertJsonPath('horas_medias_resolucion', 5.0)
            ->assertJsonPath('total', 3);
    }
}
