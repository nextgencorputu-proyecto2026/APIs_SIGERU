<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('credenciales', function (Blueprint $table) {
            $table->unsignedInteger('idUsu')->primary();
            $table->string('mail')->unique();
            $table->string('contrasena');
        });

        DB::table('credenciales')->insert([
            'idUsu' => 1,
            'mail' => 'admin@sigeru.test',
            'contrasena' => Hash::make('ClaveSegura123'),
        ]);
    }

    private function obtenerToken(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertOk();

        return $response->json('data.access_token');
    }

    public function test_login_correcto_devuelve_token_jwt(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Authenticated')
            ->assertJsonPath('data.token_type', 'bearer')
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);

        $this->assertNotEmpty(
            $response->json('data.access_token')
        );
    }

    public function test_login_rechaza_credenciales_incorrectas(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveIncorrecta',
        ])->assertUnauthorized();
    }

    public function test_login_rechaza_correo_inexistente(): void
    {
        $this->postJson('/api/auth/login', [
            'email' => 'inexistente@sigeru.test',
            'password' => 'ClaveSegura123',
        ])->assertUnauthorized();
    }

    public function test_login_valida_campos_obligatorios(): void
    {
        $this->postJson('/api/auth/login', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'email',
                'password',
            ]);
    }

    public function test_me_rechaza_usuario_no_autenticado(): void
    {
        $this->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_me_devuelve_usuario_autenticado(): void
    {
        $token = $this->obtenerToken();

        $this->withToken($token)
            ->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.idUsu', 1)
            ->assertJsonPath('data.mail', 'admin@sigeru.test')
            ->assertJsonMissingPath('data.contrasena');
    }

    public function test_refresh_renueva_token_valido(): void
    {
        $token = $this->obtenerToken();

        $response = $this->withToken($token)
            ->postJson('/api/auth/refresh');

        $response->assertOk()
            ->assertJsonPath('message', 'Authenticated')
            ->assertJsonPath('data.token_type', 'bearer')
            ->assertJsonStructure([
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                ],
            ]);

        $this->assertNotEmpty(
            $response->json('data.access_token')
        );
    }

    public function test_refresh_rechaza_token_invalido(): void
    {
        $this->withToken('token-invalido')
            ->postJson('/api/auth/refresh')
            ->assertStatus(401);
    }
}
