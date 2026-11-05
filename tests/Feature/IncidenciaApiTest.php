<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class IncidenciaApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('idUsu');
            $table->string('tipo');
        });

        Schema::create('credenciales', function (Blueprint $table) {
            $table->unsignedInteger('idUsu')->primary();
            $table->string('mail')->unique();
            $table->string('contrasena');
        });

        Schema::create('vehiculos', function (Blueprint $table) {
            $table->increments('idVehiculo');
            $table->string('matricula')->nullable();
            $table->string('marca')->nullable();
            $table->string('tipo')->nullable();
            $table->string('estado');
            $table->decimal('capacidad', 10, 2)->nullable();
        });

        Schema::create('contenedor', function (Blueprint $table) {
            $table->increments('idContenedor');
            $table->decimal('ubicacionX', 10, 4)->nullable();
            $table->decimal('ubicacionY', 10, 4)->nullable();
            $table->string('estado')->nullable();
            $table->integer('nivelLlenado')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedInteger('idRuta')->nullable();
        });

        Schema::create('cuadrilla', function (Blueprint $table) {
            $table->increments('idCuadrilla');
        });

        Schema::create('incidencia', function (Blueprint $table) {
            $table->increments('idIncidencia');
            $table->string('tipo');
            $table->string('prioridad');
            $table->string('estado');
            $table->dateTime('fechaCreacion')->nullable();
            $table->dateTime('fechaResolucion')->nullable();
        });

        Schema::create('vehiculo_presenta', function (Blueprint $table) {
            $table->unsignedInteger('idIncidencia');
            $table->unsignedInteger('idVehiculo');
        });

        Schema::create('contenedor_presenta', function (Blueprint $table) {
            $table->unsignedInteger('idIncidencia');
            $table->unsignedInteger('idContenedor');
        });

        Schema::create('reporta', function (Blueprint $table) {
            $table->unsignedInteger('idIncidencia');
            $table->unsignedInteger('idUsu');
        });

        Schema::create('controla', function (Blueprint $table) {
            $table->unsignedInteger('idIncidencia');
            $table->unsignedInteger('idUsu');
        });

        Schema::create('cuadrilla_vehiculo', function (Blueprint $table) {
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idVehiculo');
        });

        Schema::create('atiende', function (Blueprint $table) {
            $table->increments('idAtencion');
            $table->unsignedInteger('idIncidencia');
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idVehiculo');
            $table->dateTime('fechaHoraIni');
            $table->dateTime('fechaHoraFin')->nullable();
        });

        Schema::create('cumple_recoleccion', function (Blueprint $table) {
            $table->increments('idCumpleRecoleccion');
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idVehiculo');
            $table->dateTime('fechaHoraFin')->nullable();
        });

        Schema::create('cumple_barredora', function (Blueprint $table) {
            $table->increments('idCumpleBarredora');
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idVehiculo');
            $table->dateTime('fechaHoraFin')->nullable();
        });

        DB::table('usuario')->insert([
            ['idUsu' => 1, 'tipo' => 'Administrador'],
            ['idUsu' => 2, 'tipo' => 'Operario'],
            ['idUsu' => 3, 'tipo' => 'Chofer'],
        ]);

        DB::table('credenciales')->insert([
            [
                'idUsu' => 1,
                'mail' => 'admin@sigeru.test',
                'contrasena' => Hash::make('ClaveAdmin123'),
            ],
            [
                'idUsu' => 2,
                'mail' => 'operario@sigeru.test',
                'contrasena' => Hash::make('ClaveOperario123'),
            ],
            [
                'idUsu' => 3,
                'mail' => 'chofer@sigeru.test',
                'contrasena' => Hash::make('ClaveChofer123'),
            ],
        ]);

        DB::table('vehiculos')->insert([
            [
                'idVehiculo' => 1,
                'matricula' => 'ABC1234',
                'marca' => 'Mercedes',
                'tipo' => 'Camioneta',
                'estado' => 'Disponible',
            ],
            [
                'idVehiculo' => 2,
                'matricula' => 'DEF5678',
                'marca' => 'Volvo',
                'tipo' => 'Camioneta',
                'estado' => 'Inhabilitado',
            ],
        ]);

        DB::table('contenedor')->insert([
            'idContenedor' => 1,
            'ubicacionX' => -34.9000,
            'ubicacionY' => -56.1500,
            'estado' => 'Disponible',
            'nivelLlenado' => 20,
            'tipo' => 'Mixtos',
        ]);

        DB::table('cuadrilla')->insert([
            ['idCuadrilla' => 1],
            ['idCuadrilla' => 2],
        ]);

        DB::table('cuadrilla_vehiculo')->insert([
            ['idCuadrilla' => 1, 'idVehiculo' => 1],
            ['idCuadrilla' => 2, 'idVehiculo' => 2],
        ]);
    }

    private function autenticar(int $id = 1): void
    {
        $this->actingAs(
            Credenciales::findOrFail($id),
            'api'
        );
    }

    private function datosContenedor(array $cambios = []): array
    {
        return array_merge([
            'tipo' => 'Roto',
            'prioridad' => 'Media',
            'idContenedor' => 1,
        ], $cambios);
    }

    private function datosVehiculo(array $cambios = []): array
    {
        return array_merge([
            'tipo' => \App\Models\Incidencia::TIPOS_VEHICULO[0],
            'prioridad' => 'Alta',
            'idVehiculo' => 1,
        ], $cambios);
    }

    private function crearIncidencia(array $datos = []): int
    {
        $this->autenticar();

        $respuesta = $this->postJson(
            '/api/incidencias',
            $this->datosContenedor($datos)
        )->assertCreated();

        return (int) $respuesta->json('data.idIncidencia');
    }

    public function test_no_autenticado_no_puede_listar_incidencias(): void
    {
        $this->getJson('/api/incidencias')
            ->assertUnauthorized();
    }

    public function test_administrador_puede_listar_incidencias(): void
    {
        $this->autenticar();

        $this->getJson('/api/incidencias')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_operario_puede_listar_incidencias(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/incidencias')
            ->assertOk();
    }

    public function test_chofer_puede_listar_incidencias(): void
    {
        $this->autenticar(3);

        $this->getJson('/api/incidencias')
            ->assertOk();
    }

    public function test_administrador_puede_crear_incidencia_contenedor(): void
    {
        $this->autenticar();

        $this->postJson(
            '/api/incidencias',
            $this->datosContenedor()
        )
            ->assertCreated()
            ->assertJsonPath('data.tipo', 'Roto')
            ->assertJsonPath('data.estado', 'Pendiente');

        $this->assertDatabaseHas('incidencia', [
            'tipo' => 'Roto',
            'prioridad' => 'Media',
            'estado' => 'Pendiente',
        ]);
    }

    public function test_operario_puede_crear_incidencia(): void
    {
        $this->autenticar(2);

        $this->postJson(
            '/api/incidencias',
            $this->datosContenedor()
        )->assertCreated();
    }

    public function test_chofer_puede_crear_incidencia(): void
    {
        $this->autenticar(3);

        $this->postJson(
            '/api/incidencias',
            $this->datosVehiculo()
        )->assertCreated();
    }

    public function test_registra_usuario_reportante(): void
    {
        $this->autenticar(2);

        $respuesta = $this->postJson(
            '/api/incidencias',
            $this->datosContenedor()
        )->assertCreated();

        $this->assertDatabaseHas('reporta', [
            'idIncidencia' => $respuesta->json('data.idIncidencia'),
            'idUsu' => 2,
        ]);
    }

    public function test_rechaza_incidencia_sin_elemento(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', [
            'tipo' => 'Roto',
            'prioridad' => 'Media',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idVehiculo');
    }

    public function test_rechaza_incidencia_con_dos_elementos(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', [
            'tipo' => 'Roto',
            'prioridad' => 'Media',
            'idVehiculo' => 1,
            'idContenedor' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idVehiculo');
    }

    public function test_rechaza_tipo_incompatible_con_elemento(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', [
            'tipo' => 'Roto',
            'prioridad' => 'Media',
            'idVehiculo' => 1,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_rechaza_prioridad_invalida(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', $this->datosContenedor([
            'prioridad' => 'Urgentisima',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('prioridad');
    }

    public function test_rechaza_elemento_inexistente(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', $this->datosContenedor([
            'idContenedor' => 999,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idContenedor');
    }

    public function test_rechaza_fechas_manuales(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', $this->datosContenedor([
            'fechaCreacion' => '2026-01-01 10:00:00',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('fechaCreacion');
    }

    public function test_rechaza_usuario_reportante_manual(): void
    {
        $this->autenticar();

        $this->postJson('/api/incidencias', $this->datosContenedor([
            'idUsu' => 3,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idUsu');
    }

    public function test_operario_no_puede_crear_incidencia_resuelta(): void
    {
        $this->autenticar(2);

        $this->postJson('/api/incidencias', $this->datosContenedor([
            'estado' => 'Resuelta',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_operario_no_puede_asignar_cuadrilla(): void
    {
        $this->autenticar(2);

        $this->postJson('/api/incidencias', $this->datosContenedor([
            'idCuadrilla' => 1,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_administrador_puede_consultar_incidencia(): void
    {
        $id = $this->crearIncidencia();

        $this->getJson("/api/incidencias/{$id}")
            ->assertOk()
            ->assertJsonPath('data.tipo', 'Roto');
    }

    public function test_consultar_incidencia_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/incidencias/999')
            ->assertNotFound();
    }

    public function test_administrador_puede_actualizar_prioridad(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'prioridad' => 'Alta',
        ])->assertOk();

        $this->assertDatabaseHas('incidencia', [
            'idIncidencia' => $id,
            'prioridad' => 'Alta',
        ]);
    }

    public function test_operario_no_puede_actualizar_incidencia(): void
    {
        $id = $this->crearIncidencia();

        $this->autenticar(2);

        $this->putJson("/api/incidencias/{$id}", [
            'prioridad' => 'Alta',
        ])->assertForbidden();
    }

    public function test_chofer_no_puede_actualizar_incidencia(): void
    {
        $id = $this->crearIncidencia();

        $this->autenticar(3);

        $this->putJson("/api/incidencias/{$id}", [
            'prioridad' => 'Alta',
        ])->assertForbidden();
    }

    public function test_administrador_puede_asignar_cuadrilla_disponible(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'idCuadrilla' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('data.estado', 'En curso');

        $this->assertDatabaseHas('atiende', [
            'idIncidencia' => $id,
            'idCuadrilla' => 1,
            'idVehiculo' => 1,
        ]);
    }

    public function test_rechaza_cuadrilla_sin_vehiculo_disponible(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'idCuadrilla' => 2,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idCuadrilla');
    }

    public function test_rechaza_estado_en_curso_sin_cuadrilla(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'estado' => 'En curso',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_administrador_puede_resolver_incidencia(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'idCuadrilla' => 1,
        ])->assertOk();

        $this->putJson("/api/incidencias/{$id}", [
            'estado' => 'Resuelta',
        ])
            ->assertOk()
            ->assertJsonPath('data.estado', 'Resuelta');

        $this->assertDatabaseHas('incidencia', [
            'idIncidencia' => $id,
            'estado' => 'Resuelta',
        ]);

        $this->assertDatabaseMissing('atiende', [
            'idIncidencia' => $id,
            'fechaHoraFin' => null,
        ]);
    }

    public function test_no_permite_cambiar_elemento_con_historial(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'idCuadrilla' => 1,
        ])->assertOk();

        $this->putJson("/api/incidencias/{$id}", [
            'idVehiculo' => 1,
            'idContenedor' => null,
            'tipo' => \App\Models\Incidencia::TIPOS_VEHICULO[0],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idVehiculo');
    }

    public function test_administrador_puede_eliminar_incidencia_sin_historial(): void
    {
        $id = $this->crearIncidencia();

        $this->deleteJson("/api/incidencias/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('incidencia', [
            'idIncidencia' => $id,
        ]);
    }

    public function test_operario_no_puede_eliminar_incidencia(): void
    {
        $id = $this->crearIncidencia();

        $this->autenticar(2);

        $this->deleteJson("/api/incidencias/{$id}")
            ->assertForbidden();
    }

    public function test_eliminar_incidencia_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->deleteJson('/api/incidencias/999')
            ->assertNotFound();
    }

    public function test_no_permite_eliminar_incidencia_con_historial(): void
    {
        $id = $this->crearIncidencia();

        $this->putJson("/api/incidencias/{$id}", [
            'idCuadrilla' => 1,
        ])->assertOk();

        $this->deleteJson("/api/incidencias/{$id}")
            ->assertStatus(409);

        $this->assertDatabaseHas('incidencia', [
            'idIncidencia' => $id,
        ]);
    }

}
