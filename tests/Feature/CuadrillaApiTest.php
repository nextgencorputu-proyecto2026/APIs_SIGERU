<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CuadrillaApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('usuario', function (Blueprint $t) {
            $t->increments('idUsu');
            $t->string('tipo');
        });

        Schema::create('credenciales', function (Blueprint $t) {
            $t->unsignedInteger('idUsu')->primary();
            $t->string('mail');
            $t->string('contrasena');
        });

        Schema::create('vehiculos', function (Blueprint $t) {
            $t->increments('idVehiculo');
            $t->string('matricula');
            $t->string('marca');
            $t->string('tipo');
            $t->string('estado');
            $t->decimal('capacidad')->nullable();
        });

        Schema::create('ruta', function (Blueprint $t) {
            $t->increments('idRuta');
            $t->string('descripcion');
            $t->string('horario');
            $t->string('tipoResiduo');
        });

        Schema::create('cuadrilla', function (Blueprint $t) {
            $t->increments('idCuadrilla');
        });

        Schema::create('integra_cuadrilla', function (Blueprint $t) {
            $t->unsignedInteger('idCuadrilla');
            $t->unsignedInteger('idUsu');
            $t->boolean('esResponsable')->default(false);
            $t->primary(['idCuadrilla', 'idUsu']);
        });

        Schema::create('cuadrilla_vehiculo', function (Blueprint $t) {
            $t->unsignedInteger('idCuadrilla');
            $t->unsignedInteger('idVehiculo');
            $t->primary(['idCuadrilla', 'idVehiculo']);
        });

        Schema::create('asigna_ruta', function (Blueprint $t) {
            $t->increments('idAsignacion');
            $t->unsignedInteger('idUsu');
            $t->unsignedInteger('idRuta');
            $t->unsignedInteger('idCuadrilla');
        });

        foreach (['atiende', 'cumple_recoleccion', 'cumple_barredora'] as $tabla) {
            Schema::create($tabla, function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('idCuadrilla');
                $t->dateTime('fechaHoraFin')->nullable();
            });
        }

        DB::table('usuario')->insert([
            ['idUsu' => 1, 'tipo' => 'Administrador'],
            ['idUsu' => 2, 'tipo' => 'Chofer'],
            ['idUsu' => 3, 'tipo' => 'Operario'],
            ['idUsu' => 4, 'tipo' => 'Operario'],
            ['idUsu' => 5, 'tipo' => 'Operario'],
            ['idUsu' => 6, 'tipo' => 'Operario'],
        ]);

        DB::table('credenciales')->insert([
            'idUsu' => 1,
            'mail' => 'admin@test.local',
            'contrasena' => bcrypt('password'),
        ]);

        DB::table('vehiculos')->insert([
            [
                'idVehiculo' => 1,
                'matricula' => 'TEST001',
                'marca' => 'Prueba',
                'tipo' => 'Camioneta',
                'estado' => 'Disponible',
                'capacidad' => null,
            ],
            [
                'idVehiculo' => 2,
                'matricula' => 'TEST002',
                'marca' => 'Prueba',
                'tipo' => 'Camioneta',
                'estado' => 'En mantenimiento',
                'capacidad' => null,
            ],
        ]);

        DB::table('ruta')->insert([
            [
                'idRuta' => 1,
                'descripcion' => 'Ruta de prueba',
                'horario' => '08:00:00',
                'tipoResiduo' => 'Mixtos',
            ],
        ]);
    }

    private function administrador(): void
    {
        $this->actingAs(Credenciales::findOrFail(1), 'api');
    }

    private function datosValidos(): array
    {
        return [
            'idVehiculo' => 1,
            'idChofer' => 2,
            'idResponsable' => 3,
            'operarios' => [4, 5],
            'rutas' => [1],
        ];
    }

    public function test_no_autenticado_no_puede_listar_cuadrillas(): void
    {
        $this->getJson('/api/cuadrillas')->assertUnauthorized();
    }

    public function test_operario_no_puede_administrar_cuadrillas(): void
    {
        DB::table('credenciales')->insert([
            'idUsu' => 3,
            'mail' => 'operario@test.local',
            'contrasena' => bcrypt('password'),
        ]);

        $this->actingAs(Credenciales::findOrFail(3), 'api');

        $this->getJson('/api/cuadrillas')->assertForbidden();
    }

    public function test_administrador_puede_listar_cuadrillas(): void
    {
        $this->administrador();

        $this->getJson('/api/cuadrillas')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_administrador_puede_crear_cuadrilla(): void
    {
        $this->administrador();

        $this->postJson('/api/cuadrillas', $this->datosValidos())
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.idVehiculo', 1)
            ->assertJsonPath('data.idChofer', 2)
            ->assertJsonPath('data.idResponsable', 3);

        $this->assertDatabaseCount('cuadrilla', 1);
        $this->assertDatabaseCount('integra_cuadrilla', 4);
        $this->assertDatabaseCount('cuadrilla_vehiculo', 1);
        $this->assertDatabaseCount('asigna_ruta', 1);
    }

    public function test_administrador_puede_consultar_cuadrilla(): void
    {
        $this->administrador();

        $id = $this->postJson('/api/cuadrillas', $this->datosValidos())
            ->assertCreated()
            ->json('data.idCuadrilla');

        $this->getJson("/api/cuadrillas/{$id}")
            ->assertOk()
            ->assertJsonPath('data.idCuadrilla', $id);
    }

    public function test_administrador_puede_actualizar_cuadrilla(): void
    {
        $this->administrador();

        $id = $this->postJson('/api/cuadrillas', $this->datosValidos())
            ->assertCreated()
            ->json('data.idCuadrilla');

        $this->putJson("/api/cuadrillas/{$id}", [
            'operarios' => [4],
        ])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.operarios', [4]);
    }

    public function test_administrador_puede_eliminar_cuadrilla(): void
    {
        $this->administrador();

        $id = $this->postJson('/api/cuadrillas', $this->datosValidos())
            ->assertCreated()
            ->json('data.idCuadrilla');

        $this->deleteJson("/api/cuadrillas/{$id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('cuadrilla', [
            'idCuadrilla' => $id,
        ]);
    }

    public function test_no_permite_vehiculo_no_disponible(): void
    {
        $this->administrador();

        $datos = $this->datosValidos();
        $datos['idVehiculo'] = 2;

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idVehiculo');

        $this->assertDatabaseCount('cuadrilla', 0);
    }

    public function test_no_permite_chofer_con_rol_incorrecto(): void
    {
        $this->administrador();

        $datos = $this->datosValidos();
        $datos['idChofer'] = 3;

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idChofer');
    }

    public function test_no_permite_responsable_con_rol_incorrecto(): void
    {
        $this->administrador();

        $datos = $this->datosValidos();
        $datos['idResponsable'] = 2;

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idResponsable');
    }

    public function test_no_permite_mas_de_dos_operarios_adicionales(): void
    {
        $this->administrador();

        $datos = $this->datosValidos();
        $datos['operarios'] = [4, 5, 6];

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('operarios');
    }

    public function test_no_permite_responsable_repetido(): void
    {
        $this->administrador();

        $datos = $this->datosValidos();
        $datos['operarios'] = [3, 4];

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('operarios');
    }

    public function test_no_permite_ruta_inexistente(): void
    {
        $this->administrador();

        $datos = $this->datosValidos();
        $datos['rutas'] = [999];

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rutas.0');
    }

    public function test_no_permite_ruta_incompatible_con_tipo_de_camion(): void
    {
        $this->administrador();

        DB::table('vehiculos')->insert([
            'idVehiculo' => 3,
            'matricula' => 'TEST003',
            'marca' => 'Prueba',
            'tipo' => 'Camión recolector mixtos',
            'estado' => 'Disponible',
            'capacidad' => 5000,
        ]);

        DB::table('ruta')->insert([
            'idRuta' => 2,
            'descripcion' => 'Ruta de reciclables',
            'horario' => '09:00:00',
            'tipoResiduo' => 'Reciclables',
        ]);

        $datos = $this->datosValidos();
        $datos['idVehiculo'] = 3;
        $datos['operarios'] = [];
        $datos['rutas'] = [2];

        $this->postJson('/api/cuadrillas', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rutas');

        $this->assertDatabaseCount('cuadrilla', 0);
    }

    public function test_no_permite_cambiar_integrantes_de_cuadrilla_en_uso(): void
    {
        $this->administrador();

        $id = $this->postJson('/api/cuadrillas', $this->datosValidos())
            ->assertCreated()
            ->json('data.idCuadrilla');

        DB::table('atiende')->insert([
            'idCuadrilla' => $id,
            'fechaHoraFin' => null,
        ]);

        $this->putJson("/api/cuadrillas/{$id}", [
            'operarios' => [4],
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idCuadrilla');

        $this->getJson("/api/cuadrillas/{$id}")
            ->assertOk()
            ->assertJsonPath('data.operarios', [4, 5]);

        $this->assertDatabaseCount('integra_cuadrilla', 4);
    }


}
