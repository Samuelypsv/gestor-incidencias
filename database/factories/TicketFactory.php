<?php

namespace Database\Factories;

use App\Enums\EstadoTicket;
use App\Enums\Prioridad;
use App\Models\Cliente;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => fake()->sentence(5),
            'descripcion' => fake()->paragraph(),
            'estado' => EstadoTicket::Abierto,
            'prioridad' => fake()->randomElement(Prioridad::cases()),
            'cliente_id' => Cliente::factory(),
            'autor_id' => User::factory(),
            'tecnico_id' => null,
        ];
    }

    public function enEstado(EstadoTicket $estado): static
    {
        return $this->state(fn () => [
            'estado' => $estado,
            'resuelto_at' => in_array($estado, [EstadoTicket::Resuelto, EstadoTicket::Cerrado], true) ? now() : null,
        ]);
    }
}
