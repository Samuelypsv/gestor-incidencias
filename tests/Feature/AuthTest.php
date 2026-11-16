<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_usuario_puede_iniciar_sesion_y_recibe_un_token(): void
    {
        User::factory()->create(['email' => 'laura@demo.test']);

        $this->postJson('/api/login', ['email' => 'laura@demo.test', 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token', 'usuario' => ['id', 'nombre', 'email', 'rol']]);
    }

    public function test_con_una_contrasena_incorrecta_devuelve_error_de_validacion(): void
    {
        User::factory()->create(['email' => 'laura@demo.test']);

        $this->postJson('/api/login', ['email' => 'laura@demo.test', 'password' => 'mal'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_la_api_exige_estar_autenticado(): void
    {
        $this->getJson('/api/tickets')->assertUnauthorized();
    }

    public function test_con_el_token_se_puede_acceder_a_la_api(): void
    {
        User::factory()->create(['email' => 'laura@demo.test']);
        $token = $this->postJson('/api/login', ['email' => 'laura@demo.test', 'password' => 'password'])->json('token');

        $this->withToken($token)->getJson('/api/yo')
            ->assertOk()
            ->assertJsonPath('data.email', 'laura@demo.test');
    }
}
