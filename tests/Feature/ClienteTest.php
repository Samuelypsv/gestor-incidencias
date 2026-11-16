<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClienteTest extends TestCase
{
    use RefreshDatabase;

    public function test_crea_un_cliente_normalizando_el_cif(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/clientes', ['nombre' => 'Talleres Salamanca SL', 'cif' => ' b37123456 '])
            ->assertCreated()
            ->assertJsonPath('data.cif', 'B37123456');
    }

    public function test_rechaza_un_cif_con_formato_incorrecto_o_repetido(): void
    {
        Sanctum::actingAs(User::factory()->create());
        Cliente::factory()->create(['cif' => 'B37123456']);

        $this->postJson('/api/clientes', ['nombre' => 'X', 'cif' => '123'])
            ->assertJsonValidationErrors('cif');

        $this->postJson('/api/clientes', ['nombre' => 'Y', 'cif' => 'B37123456'])
            ->assertJsonValidationErrors('cif');
    }

    public function test_no_se_puede_borrar_un_cliente_con_tickets(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $cliente = Ticket::factory()->create()->cliente;

        $this->deleteJson("/api/clientes/{$cliente->id}")->assertConflict();
    }
}
