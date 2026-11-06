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

test('listar contenedores', function () {
    $this->getJson('/api/contenedores')
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('crear un contenedor', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $this->postJson('/api/contenedores', [
        'ubicacionX' => -34.9000,
        'ubicacionY' => -56.1500,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('contenedor', [
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ]);
});

test('modificar un contenedor', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $id = DB::table('contenedor')->insertGetId([
        'ubicacionX' => -34.9000,
        'ubicacionY' => -56.1500,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ]);

    $this->putJson("/api/contenedores/$id", [
        'nivelLlenado' => 80,
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('contenedor', [
        'idContenedor' => $id,
        'nivelLlenado' => 80,
    ]);
});

test('eliminar un contenedor', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $id = DB::table('contenedor')->insertGetId([
        'ubicacionX' => -34.9000,
        'ubicacionY' => -56.1500,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ]);

    $this->deleteJson("/api/contenedores/$id")
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('contenedor', [
        'idContenedor' => $id,
    ]);
});

test('consultar un contenedor por ID', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $id = DB::table('contenedor')->insertGetId([
        'ubicacionX' => -34.9000,
        'ubicacionY' => -56.1500,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ]);

    $this->getJson("/api/contenedores/$id")
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.idContenedor', $id);
});


test('no eliminar un contenedor con incidencias', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $id = DB::table('contenedor')->insertGetId([
        'ubicacionX' => -34.9000,
        'ubicacionY' => -56.1500,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ]);

    $idIncidencia = DB::table('incidencia')->insertGetId([
        'tipo' => 'Incendiado',
        'prioridad' => 'Alta',
        'estado' => 'Pendiente',
        'fechaCreacion' => now(),
    ]);

    DB::table('contenedor_presenta')->insert([
        'idContenedor' => $id,
        'idIncidencia' => $idIncidencia,
    ]);

    $this->deleteJson("/api/contenedores/$id")
        ->assertStatus(409);

    $this->assertDatabaseHas('contenedor', [
        'idContenedor' => $id,
    ]);
});
