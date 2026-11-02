<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshSigeruDatabase;
use Tests\TestCase;

class UsuarioApiTest extends TestCase
{
    use RefreshSigeruDatabase;

    private function autenticarAdministrador(): void
    {
        DB::table('centro')->insert([
            'idCentro' => 1,
        ]);

        DB::table('usuario')->insert([
            'idUsu' => 1,
            'ci' => '12345678',
            'nombre1' => 'Administrador',
            'nombre2' => null,
            'apellido1' => 'Prueba',
            'apellido2' => null,
            'fec_nac' => '1995-01-01',
            'tipo' => 'Administrador',
            'idCentro' => 1,
        ]);

        DB::table('credenciales')->insert([
            'idUsu' => 1,
            'mail' => 'admin@sigeru.test',
            'contrasena' => Hash::make('ClaveSegura123'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $login->assertStatus(200);

        $this->withToken(
            $login->json('data.access_token')
        );
    }

    public function test_administrador_puede_listar_usuarios(): void
    {
        $this->autenticarAdministrador();

        $response = $this->getJson('/api/usuarios');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre1', 'Administrador')
            ->assertJsonPath('data.0.mail', 'admin@sigeru.test');
    }

    public function test_administrador_puede_crear_usuario(): void
    {
        $this->autenticarAdministrador();

        $response = $this->postJson('/api/usuarios', [
            'ci' => '87654321',
            'nombre1' => 'Juan',
            'nombre2' => null,
            'apellido1' => 'Perez',
            'apellido2' => null,
            'fec_nac' => '2000-05-15',
            'tipo' => 'Operario',
            'idCentro' => 1,
            'email' => 'juan@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'mensaje',
                'Usuario registrado correctamente'
            );

        $this->assertDatabaseHas('usuario', [
            'ci' => '87654321',
            'nombre1' => 'Juan',
            'apellido1' => 'Perez',
            'tipo' => 'Operario',
        ]);

        $this->assertDatabaseHas('credenciales', [
            'mail' => 'juan@sigeru.test',
        ]);

        $credencial = DB::table('credenciales')
            ->where('mail', 'juan@sigeru.test')
            ->first();

        $this->assertTrue(
            Hash::check('ClaveSegura123', $credencial->contrasena)
        );
    }

    public function test_no_permite_registrar_cedula_duplicada(): void
    {
        $this->autenticarAdministrador();

        $response = $this->postJson('/api/usuarios', [
            'ci' => '12345678',
            'nombre1' => 'Pedro',
            'nombre2' => null,
            'apellido1' => 'Gomez',
            'apellido2' => null,
            'fec_nac' => '2000-05-15',
            'tipo' => 'Operario',
            'idCentro' => 1,
            'email' => 'pedro@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['ci']);

        $this->assertDatabaseCount('usuario', 1);

        $this->assertDatabaseCount('credenciales', 1);
    }

    public function test_no_permite_registrar_correo_duplicado(): void
    {
        $this->autenticarAdministrador();

        $response = $this->postJson('/api/usuarios', [
            'ci' => '87654321',
            'nombre1' => 'Pedro',
            'nombre2' => null,
            'apellido1' => 'Gomez',
            'apellido2' => null,
            'fec_nac' => '2000-05-15',
            'tipo' => 'Operario',
            'idCentro' => 1,
            'email' => 'admin@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseCount('usuario', 1);

        $this->assertDatabaseCount('credenciales', 1);
    }

    public function test_operario_no_puede_listar_usuarios(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')
            ->where('idUsu', 1)
            ->update([
                'tipo' => 'Operario',
            ]);

        $response = $this->getJson('/api/usuarios');

        $response->assertStatus(403);
    }


    public function test_administrador_puede_consultar_usuario_por_id(): void
    {
        $this->autenticarAdministrador();

        $response = $this->getJson('/api/usuarios/1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.idUsu', 1)
            ->assertJsonPath('data.nombre1', 'Administrador')
            ->assertJsonPath('data.apellido1', 'Prueba')
            ->assertJsonPath('data.tipo', 'Administrador');
    }

    public function test_administrador_puede_actualizar_usuario(): void
    {
        $this->autenticarAdministrador();

        $response = $this->putJson('/api/usuarios/1', [
            'nombre1' => 'Sebastian',
            'apellido1' => 'Ovelar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('mensaje', 'Usuario actualizado correctamente')
            ->assertJsonPath('data.nombre1', 'Sebastian')
            ->assertJsonPath('data.apellido1', 'Ovelar');

        $this->assertDatabaseHas('usuario', [
            'idUsu' => 1,
            'nombre1' => 'Sebastian',
            'apellido1' => 'Ovelar',
        ]);
    }

    public function test_administrador_puede_eliminar_usuario(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')->insert([
            'idUsu' => 2,
            'ci' => '87654321',
            'nombre1' => 'Pedro',
            'nombre2' => null,
            'apellido1' => 'Gomez',
            'apellido2' => null,
            'fec_nac' => '2000-05-15',
            'tipo' => 'Operario',
            'idCentro' => 1,
        ]);

        $response = $this->deleteJson('/api/usuarios/2');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'mensaje',
                'Usuario eliminado correctamente'
            );

        $this->assertDatabaseMissing('usuario', [
            'idUsu' => 2,
        ]);

        $this->assertDatabaseHas('usuario', [
            'idUsu' => 1,
        ]);
    }

    public function test_consultar_usuario_inexistente_devuelve_404(): void
    {
        $this->autenticarAdministrador();

        $response = $this->getJson('/api/usuarios/9999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'mensaje',
                'Usuario no encontrado'
            );
    }

    public function test_eliminar_usuario_inexistente_devuelve_404(): void
    {
        $this->autenticarAdministrador();

        $response = $this->deleteJson('/api/usuarios/9999');

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath(
                'mensaje',
                'Usuario no encontrado'
            );

        $this->assertDatabaseCount('usuario', 1);
    }



}
