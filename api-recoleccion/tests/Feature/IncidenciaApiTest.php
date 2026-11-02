<?php

use App\Models\Credenciales;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\RefreshSigeruDatabase;
use App\Services\TelegramService;
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
    $this->app->instance(
        TelegramService::class,
        \Mockery::mock(TelegramService::class, function ($mock) {
            $mock->shouldReceive('enviarMensaje')->zeroOrMoreTimes();
        })
    );
});

test('listar incidencias', function () {
    $this->getJson('/api/incidencias')
        ->assertOk()
        ->assertJsonPath('success', true);
});

test('crear una incidencia', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $idContenedor = DB::table('contenedor')->insertGetId([
        'ubicacionX' => -34.9000,
        'ubicacionY' => -56.1500,
        'estado' => 'Disponible',
        'nivelLlenado' => 0,
        'tipo' => 'Mixtos',
        'idRuta' => $idRuta,
    ]);

    $this->postJson('/api/incidencias', [
        'tipo' => 'Incendiado',
        'prioridad' => 'Alta',
        'idContenedor' => $idContenedor,
    ])
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('incidencia', [
        'tipo' => 'Incendiado',
        'prioridad' => 'Alta',
        'estado' => 'Pendiente',
    ]);
});

test('modificar una incidencia', function () {
    $idRuta = DB::table('ruta')->insertGetId([
        'descripcion' => 'Ruta Buceo',
        'horario' => '08:00',
        'tipoResiduo' => 'Mixtos',
    ]);

    $idContenedor = DB::table('contenedor')->insertGetId([
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
        'idIncidencia' => $idIncidencia,
        'idContenedor' => $idContenedor,
    ]);

    $this->putJson("/api/incidencias/{$idIncidencia}", [
        'prioridad' => 'Media',
    ])
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('incidencia', [
        'idIncidencia' => $idIncidencia,
        'prioridad' => 'Media',
    ]);
});

test('eliminar una incidencia', function () {
    $idIncidencia = DB::table('incidencia')->insertGetId([
        'tipo' => 'Incendiado',
        'prioridad' => 'Alta',
        'estado' => 'Pendiente',
        'fechaCreacion' => now(),
    ]);

    $this->deleteJson("/api/incidencias/{$idIncidencia}")
        ->assertOk()
        ->assertJsonPath('success', true);

    $this->assertDatabaseMissing('incidencia', [
        'idIncidencia' => $idIncidencia,
    ]);
});
