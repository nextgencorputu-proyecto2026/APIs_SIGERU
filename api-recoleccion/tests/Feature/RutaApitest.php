<?php

use App\Models\Credenciales;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\RefreshSigeruDatabase;

uses(RefreshSigeruDatabase::class);

beforeEach(function () {
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
        'mail' => 'admin.testing@example.com',
        'contrasena' => bcrypt('password123'),
    ]);

    $this->actingAs(Credenciales::findOrFail(1), 'api');
});

test('listar rutas', function () {
    $this->getJson('/api/rutas')
        ->assertOk();
});

test('filtrar rutas por tipo de residuo', function () {
    $this->getJson('/api/rutas?tipoResiduo=Mixtos')
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('crear una ruta', function () {
    $this->postJson('/api/rutas', [
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('ruta', [
        'descripcion' => 'Ruta Buceo',
        'tipoResiduo' => 'Mixtos',
    ]);
});

test('modificar una ruta', function () {
    $id = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Original',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $this->putJson("/api/rutas/$id", [
        'descripcion' => 'Ruta Modificada',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('ruta', [
        'idRuta' => $id,
        'descripcion' => 'Ruta Modificada',
    ]);
});

test('no eliminar una ruta con contenedores asociados', function () {
    $id = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Ocupada',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    DB::table('contenedor')->insert([
        'ubicacionX' => -34.90,
        'ubicacionY' => -56.15,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $id,
    ]);

    $this->deleteJson("/api/rutas/$id")
        ->assertStatus(409);

    $this->assertDatabaseHas('ruta', [
        'idRuta' => $id,
    ]);
});

test('eliminar una ruta', function () {
    $id = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta para eliminar',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $this->deleteJson("/api/rutas/$id")
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('ruta', [
        'idRuta' => $id,
    ]);
});
