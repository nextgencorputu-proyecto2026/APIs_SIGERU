<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshSigeruDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshSigeruDatabase;

    private function crearUsuario(): void
    {
        DB::table('centro')->insert([
            'idCentro' => 1,
        ]);

        DB::table('usuario')->insert([
            'idUsu' => 1,
            'ci' => '12345678',
            'nombre1' => 'Usuario',
            'nombre2' => null,
            'apellido1' => 'Prueba',
            'apellido2' => null,
            'fec_nac' => '2000-01-01',
            'tipo' => 'Administrador',
            'idCentro' => 1,
        ]);

        Credenciales::create([
            'idUsu' => 1,
            'mail' => 'admin@sigeru.test',
            'contrasena' => Hash::make('ClaveSegura123'),
        ]);
    }

    public function test_login_correcto_devuelve_token_jwt(): void
    {
        $this->crearUsuario();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Authenticated')
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

    public function test_login_incorrecto_devuelve_401(): void
    {
        $this->crearUsuario();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveIncorrecta123',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath(
                'message',
                'Credenciales inválidas'
            );
    }

    public function test_auth_me_sin_token_devuelve_401(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_auth_me_con_token_devuelve_datos_del_usuario(): void
    {
        $this->crearUsuario();

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $login->assertStatus(200);

        $token = $login->json('data.access_token');

        $response = $this->withToken($token)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.idUsu', 1)
            ->assertJsonPath('data.mail', 'admin@sigeru.test')
            ->assertJsonPath('data.nombre', 'Usuario')
            ->assertJsonPath('data.apellido', 'Prueba')
            ->assertJsonPath('data.tipo', 'Administrador');
    }

}
