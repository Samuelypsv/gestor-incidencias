<?php

namespace Database\Factories;

use App\Models\Comentario;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comentario>
 */
class ComentarioFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'autor_id' => User::factory(),
            'texto' => fake()->sentence(12),
        ];
    }
}
