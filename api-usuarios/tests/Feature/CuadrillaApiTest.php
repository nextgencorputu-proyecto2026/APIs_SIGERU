<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\RefreshSigeruDatabase;
use Tests\TestCase;

class CuadrillaApiTest extends TestCase
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
            'apellido1' => 'Prueba',
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

    public function test_administrador_puede_listar_cuadrillas(): void
    {
        $this->autenticarAdministrador();

        $response = $this->getJson('/api/cuadrillas');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }

    public function test_administrador_puede_crear_cuadrilla(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')->insert([
            [
                'idUsu' => 2,
                'ci' => '87654321',
                'nombre1' => 'Carlos',
                'apellido1' => 'Chofer',
                'fec_nac' => '1990-01-01',
                'tipo' => 'Chofer',
                'idCentro' => 1,
            ],
            [
                'idUsu' => 3,
                'ci' => '11223344',
                'nombre1' => 'Pedro',
                'apellido1' => 'Operario',
                'fec_nac' => '1992-01-01',
                'tipo' => 'Operario',
                'idCentro' => 1,
            ],
        ]);

        DB::table('vehiculos')->insert([
            'idVehiculo' => 1,
            'matricula' => 'ABC1234',
            'marca' => 'Mercedes',
            'tipo' => 'Camión recolector mixtos',
            'estado' => 'Disponible',
        ]);

        DB::table('ruta')->insert([
            'idRuta' => 1,
            'tipoResiduo' => 'Mixtos',
        ]);

        $response = $this->postJson('/api/cuadrillas', [
            'idVehiculo' => 1,
            'idChofer' => 2,
            'idResponsable' => 3,
            'operarios' => [],
            'rutas' => [1],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.idVehiculo', 1)
            ->assertJsonPath('data.idChofer', 2)
            ->assertJsonPath('data.idResponsable', 3)
            ->assertJsonPath('data.rutas.0', 1);

        $this->assertDatabaseCount('cuadrilla', 1);

        $this->assertDatabaseHas('integra_cuadrilla', [
            'idCuadrilla' => 1,
            'idUsu' => 3,
            'esResponsable' => 1,
        ]);

        $this->assertDatabaseHas('cuadrilla_vehiculo', [
            'idCuadrilla' => 1,
            'idVehiculo' => 1,
        ]);

        $this->assertDatabaseHas('asigna_ruta', [
            'idCuadrilla' => 1,
            'idRuta' => 1,
            'idUsu' => 1,
        ]);
    }

    public function test_no_permite_crear_cuadrilla_con_vehiculo_no_disponible(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')->insert([
            [
                'idUsu' => 2,
                'ci' => '87654321',
                'nombre1' => 'Carlos',
                'apellido1' => 'Chofer',
                'fec_nac' => '1990-01-01',
                'tipo' => 'Chofer',
                'idCentro' => 1,
            ],
            [
                'idUsu' => 3,
                'ci' => '11223344',
                'nombre1' => 'Pedro',
                'apellido1' => 'Operario',
                'fec_nac' => '1992-01-01',
                'tipo' => 'Operario',
                'idCentro' => 1,
            ],
        ]);

        DB::table('vehiculos')->insert([
            'idVehiculo' => 1,
            'matricula' => 'ABC1234',
            'marca' => 'Mercedes',
            'tipo' => 'Camión recolector mixtos',
            'estado' => 'En mantenimiento',
        ]);

        $response = $this->postJson('/api/cuadrillas', [
            'idVehiculo' => 1,
            'idChofer' => 2,
            'idResponsable' => 3,
            'operarios' => [],
            'rutas' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['idVehiculo']);

        $this->assertDatabaseCount('cuadrilla', 0);
    }

    public function test_no_permite_asignar_ruta_incompatible_con_vehiculo(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')->insert([
            [
                'idUsu' => 2,
                'ci' => '87654321',
                'nombre1' => 'Carlos',
                'apellido1' => 'Chofer',
                'fec_nac' => '1990-01-01',
                'tipo' => 'Chofer',
                'idCentro' => 1,
            ],
            [
                'idUsu' => 3,
                'ci' => '11223344',
                'nombre1' => 'Pedro',
                'apellido1' => 'Operario',
                'fec_nac' => '1992-01-01',
                'tipo' => 'Operario',
                'idCentro' => 1,
            ],
        ]);

        DB::table('vehiculos')->insert([
            'idVehiculo' => 1,
            'matricula' => 'ABC1234',
            'marca' => 'Mercedes',
            'tipo' => 'Camión recolector mixtos',
            'estado' => 'Disponible',
        ]);

        DB::table('ruta')->insert([
            'idRuta' => 1,
            'tipoResiduo' => 'Reciclables',
        ]);

        $response = $this->postJson('/api/cuadrillas', [
            'idVehiculo' => 1,
            'idChofer' => 2,
            'idResponsable' => 3,
            'operarios' => [],
            'rutas' => [1],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rutas']);

        $this->assertDatabaseCount('cuadrilla', 0);
    }

    public function test_administrador_puede_consultar_cuadrilla_por_id(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')->insert([
            [
                'idUsu' => 2,
                'ci' => '87654321',
                'nombre1' => 'Carlos',
                'apellido1' => 'Chofer',
                'fec_nac' => '1990-01-01',
                'tipo' => 'Chofer',
                'idCentro' => 1,
            ],
            [
                'idUsu' => 3,
                'ci' => '11223344',
                'nombre1' => 'Pedro',
                'apellido1' => 'Operario',
                'fec_nac' => '1992-01-01',
                'tipo' => 'Operario',
                'idCentro' => 1,
            ],
        ]);

        DB::table('vehiculos')->insert([
            'idVehiculo' => 1,
            'matricula' => 'ABC1234',
            'marca' => 'Mercedes',
            'tipo' => 'Camión recolector mixtos',
            'estado' => 'Disponible',
        ]);

        DB::table('ruta')->insert([
            'idRuta' => 1,
            'tipoResiduo' => 'Mixtos',
        ]);

        DB::table('cuadrilla')->insert([
            'idCuadrilla' => 1,
        ]);

        DB::table('integra_cuadrilla')->insert([
            [
                'idCuadrilla' => 1,
                'idUsu' => 2,
                'esResponsable' => 0,
            ],
            [
                'idCuadrilla' => 1,
                'idUsu' => 3,
                'esResponsable' => 1,
            ],
        ]);

        DB::table('cuadrilla_vehiculo')->insert([
            'idCuadrilla' => 1,
            'idVehiculo' => 1,
        ]);

        DB::table('asigna_ruta')->insert([
            'idCuadrilla' => 1,
            'idRuta' => 1,
            'idUsu' => 1,
        ]);

        $response = $this->getJson('/api/cuadrillas/1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.idCuadrilla', 1)
            ->assertJsonPath('data.idVehiculo', 1)
            ->assertJsonPath('data.idChofer', 2)
            ->assertJsonPath('data.idResponsable', 3)
            ->assertJsonPath('data.rutas.0', 1);
    }

    public function test_administrador_puede_actualizar_rutas_de_cuadrilla(): void
    {
        $this->autenticarAdministrador();

        DB::table('usuario')->insert([
            [
                'idUsu' => 2,
                'ci' => '87654321',
                'nombre1' => 'Carlos',
                'apellido1' => 'Chofer',
                'fec_nac' => '1990-01-01',
                'tipo' => 'Chofer',
                'idCentro' => 1,
            ],
            [
                'idUsu' => 3,
                'ci' => '11223344',
                'nombre1' => 'Pedro',
                'apellido1' => 'Operario',
                'fec_nac' => '1992-01-01',
                'tipo' => 'Operario',
                'idCentro' => 1,
            ],
        ]);

        DB::table('vehiculos')->insert([
            'idVehiculo' => 1,
            'matricula' => 'ABC1234',
            'marca' => 'Mercedes',
            'tipo' => 'Camión recolector mixtos',
            'estado' => 'Disponible',
        ]);

        DB::table('ruta')->insert([
            [
                'idRuta' => 1,
                'tipoResiduo' => 'Mixtos',
            ],
            [
                'idRuta' => 2,
                'tipoResiduo' => 'Mixtos',
            ],
        ]);

        DB::table('cuadrilla')->insert([
            'idCuadrilla' => 1,
        ]);

        DB::table('integra_cuadrilla')->insert([
            [
                'idCuadrilla' => 1,
                'idUsu' => 2,
                'esResponsable' => 0,
            ],
            [
                'idCuadrilla' => 1,
                'idUsu' => 3,
                'esResponsable' => 1,
            ],
        ]);

        DB::table('cuadrilla_vehiculo')->insert([
            'idCuadrilla' => 1,
            'idVehiculo' => 1,
        ]);

        DB::table('asigna_ruta')->insert([
            'idCuadrilla' => 1,
            'idRuta' => 1,
            'idUsu' => 1,
        ]);

        $response = $this->putJson('/api/cuadrillas/1', [
            'idVehiculo' => 1,
            'idChofer' => 2,
            'idResponsable' => 3,
            'operarios' => [],
            'rutas' => [2],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath(
                'mensaje',
                'Cuadrilla actualizada correctamente'
            )
            ->assertJsonPath('data.rutas.0', 2);

        $this->assertDatabaseHas('asigna_ruta', [
            'idCuadrilla' => 1,
            'idRuta' => 2,
        ]);

        $this->assertDatabaseMissing('asigna_ruta', [
            'idCuadrilla' => 1,
            'idRuta' => 1,
        ]);
    }

    public function test_administrador_puede_eliminar_cuadrilla(): void
    {
        $this->autenticarAdministrador();

        DB::table('cuadrilla')->insert([
            'idCuadrilla' => 1,
        ]);

        $this->assertDatabaseHas('cuadrilla', [
            'idCuadrilla' => 1,
        ]);

        $response = $this->deleteJson('/api/cuadrillas/1');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('cuadrilla', [
            'idCuadrilla' => 1,
        ]);

        $this->assertDatabaseCount('cuadrilla', 0);
    }

    public function test_no_permite_eliminar_cuadrilla_con_trabajo_activo(): void
    {
        $this->autenticarAdministrador();

        DB::table('cuadrilla')->insert([
            'idCuadrilla' => 1,
        ]);

        DB::table('atiende')->insert([
            'idCuadrilla' => 1,
            'fechaHoraFin' => null,
        ]);

        $response = $this->deleteJson('/api/cuadrillas/1');

        $response->assertStatus(409);

        $this->assertDatabaseHas('cuadrilla', [
            'idCuadrilla' => 1,
        ]);

        $this->assertDatabaseCount('cuadrilla', 1);
    }

    public function test_operario_no_puede_listar_cuadrillas(): void
    {
        DB::table('centro')->insert([
            'idCentro' => 1,
        ]);

        DB::table('usuario')->insert([
            'idUsu' => 2,
            'ci' => '87654321',
            'nombre1' => 'Pedro',
            'apellido1' => 'Operario',
            'fec_nac' => '1992-01-01',
            'tipo' => 'Operario',
            'idCentro' => 1,
        ]);

        DB::table('credenciales')->insert([
            'idUsu' => 2,
            'mail' => 'operario@sigeru.test',
            'contrasena' => Hash::make('ClaveSegura123'),
        ]);

        $login = $this->postJson('/api/auth/login', [
            'email' => 'operario@sigeru.test',
            'password' => 'ClaveSegura123',
        ]);

        $login->assertStatus(200);

        $this->withToken($login->json('data.access_token'));

        $response = $this->getJson('/api/cuadrillas');

        $response->assertStatus(403);
    }

public function test_consultar_cuadrilla_inexistente_devuelve_404(): void
{
    $this->autenticarAdministrador();

    $response = $this->getJson('/api/cuadrillas/9999');

    $response->assertStatus(404);

    $this->assertDatabaseCount('cuadrilla', 0);
}

public function test_eliminar_cuadrilla_inexistente_devuelve_404(): void
{
    $this->autenticarAdministrador();

    $response = $this->deleteJson('/api/cuadrillas/9999');

    $response->assertStatus(404);

    $this->assertDatabaseCount('cuadrilla', 0);
}


}
