<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cliente>
 */
class ClienteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'cif' => fake()->unique()->regexify('[ABG][0-9]{8}'),
            'email' => fake()->companyEmail(),
            'telefono' => fake()->numerify('9########'),
        ];
    }
}
