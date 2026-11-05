<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CamionApiTest extends TestCase
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
            $table->string('matricula', 12)->unique();
            $table->string('marca', 40);
            $table->string('tipo');
            $table->string('estado');
            $table->decimal('capacidad', 10, 2)->nullable();
        });

        DB::table('usuario')->insert([
            ['idUsu' => 1, 'tipo' => 'Administrador'],
            ['idUsu' => 2, 'tipo' => 'Operario'],
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
        ]);

        DB::table('vehiculos')->insert([
            'idVehiculo' => 1,
            'matricula' => 'ABC1234',
            'marca' => 'Mercedes',
            'tipo' => 'Camioneta',
            'estado' => 'Disponible',
            'capacidad' => null,
        ]);
    }

    private function autenticar(int $id = 1): void
    {
        $this->actingAs(
            Credenciales::findOrFail($id),
            'api'
        );
    }

    private function datosVehiculo(array $cambios = []): array
    {
        return array_merge([
            'matricula' => 'DEF5678',
            'marca' => 'Volvo',
            'tipo' => 'Camioneta',
            'estado' => 'Disponible',
        ], $cambios);
    }

    public function test_no_autenticado_no_puede_listar_camiones(): void
    {
        $this->getJson('/api/camiones')
            ->assertUnauthorized();
    }

    public function test_operario_no_puede_listar_camiones(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/camiones')
            ->assertForbidden();
    }

    public function test_operario_no_puede_crear_camiones(): void
    {
        $this->autenticar(2);

        $this->postJson(
            '/api/camiones',
            $this->datosVehiculo()
        )->assertForbidden();
    }

    public function test_operario_no_puede_modificar_camiones(): void
    {
        $this->autenticar(2);

        $this->putJson('/api/camiones/1', [
            'marca' => 'Otra marca',
        ])->assertForbidden();
    }

    public function test_operario_no_puede_eliminar_camiones(): void
    {
        $this->autenticar(2);

        $this->deleteJson('/api/camiones/1')
            ->assertForbidden();
    }

    public function test_administrador_puede_listar_camiones(): void
    {
        $this->autenticar();

        $this->getJson('/api/camiones')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_administrador_puede_crear_camioneta(): void
    {
        $this->autenticar();

        $this->postJson(
            '/api/camiones',
            $this->datosVehiculo()
        )->assertCreated();

        $this->assertDatabaseHas('vehiculos', [
            'matricula' => 'DEF5678',
            'tipo' => 'Camioneta',
            'capacidad' => null,
        ]);
    }

    public function test_camion_recolector_requiere_capacidad(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'tipo' => 'Camión recolector mixtos',
        ]))->assertUnprocessable();
    }

    public function test_camion_recolector_acepta_capacidad_valida(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'tipo' => 'Camión recolector mixtos',
            'capacidad' => 12.5,
        ]))->assertCreated();

        $this->assertDatabaseHas('vehiculos', [
            'matricula' => 'DEF5678',
            'capacidad' => 12.5,
        ]);
    }

    public function test_camioneta_ignora_capacidad(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'capacidad' => 15,
        ]))->assertCreated();

        $this->assertDatabaseHas('vehiculos', [
            'matricula' => 'DEF5678',
            'capacidad' => null,
        ]);
    }

    public function test_barredora_no_guarda_capacidad(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'tipo' => 'Barredora',
            'capacidad' => 10,
        ]))->assertCreated();

        $this->assertDatabaseHas('vehiculos', [
            'matricula' => 'DEF5678',
            'capacidad' => null,
        ]);
    }

    public function test_no_permite_matricula_duplicada(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'matricula' => 'ABC1234',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('matricula');
    }

    public function test_no_permite_tipo_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'tipo' => 'Motocicleta',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_no_permite_estado_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'estado' => 'Desconocido',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_no_permite_capacidad_negativa(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', $this->datosVehiculo([
            'capacidad' => -5,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('capacidad');
    }

    public function test_valida_campos_obligatorios(): void
    {
        $this->autenticar();

        $this->postJson('/api/camiones', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'matricula',
                'marca',
                'tipo',
                'estado',
            ]);
    }

    public function test_administrador_puede_consultar_camion(): void
    {
        $this->autenticar();

        $this->getJson('/api/camiones/1')
            ->assertOk()
            ->assertJsonPath('data.matricula', 'ABC1234');
    }

    public function test_consultar_camion_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/camiones/999')
            ->assertNotFound();
    }

    public function test_administrador_puede_actualizar_camion(): void
    {
        $this->autenticar();

        $this->putJson('/api/camiones/1', [
            'marca' => 'Iveco',
        ])->assertOk();

        $this->assertDatabaseHas('vehiculos', [
            'idVehiculo' => 1,
            'marca' => 'Iveco',
        ]);
    }

    public function test_actualizar_camion_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->putJson('/api/camiones/999', [
            'marca' => 'Iveco',
        ])->assertNotFound();
    }

    public function test_actualizacion_rechaza_matricula_duplicada(): void
    {
        $this->autenticar();

        DB::table('vehiculos')->insert([
            'matricula' => 'XYZ9999',
            'marca' => 'Ford',
            'tipo' => 'Camioneta',
            'estado' => 'Disponible',
            'capacidad' => null,
        ]);

        $this->putJson('/api/camiones/1', [
            'matricula' => 'XYZ9999',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('matricula');
    }

    public function test_actualizacion_a_camion_requiere_capacidad(): void
    {
        $this->autenticar();

        $this->putJson('/api/camiones/1', [
            'tipo' => 'Camión recolector mixtos',
        ])->assertUnprocessable();
    }

    public function test_actualizacion_a_barredora_limpia_capacidad(): void
    {
        $this->autenticar();

        DB::table('vehiculos')
            ->where('idVehiculo', 1)
            ->update([
                'tipo' => 'Camión recolector mixtos',
                'capacidad' => 12,
            ]);

        $this->putJson('/api/camiones/1', [
            'tipo' => 'Barredora',
        ])->assertOk();

        $this->assertDatabaseHas('vehiculos', [
            'idVehiculo' => 1,
            'capacidad' => null,
        ]);
    }

    public function test_administrador_puede_eliminar_camion(): void
    {
        $this->autenticar();

        $this->deleteJson('/api/camiones/1')
            ->assertOk();

        $this->assertDatabaseMissing('vehiculos', [
            'idVehiculo' => 1,
        ]);
    }

    public function test_eliminar_camion_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->deleteJson('/api/camiones/999')
            ->assertNotFound();
    }
}
