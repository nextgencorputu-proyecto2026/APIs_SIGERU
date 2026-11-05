<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MaquinariaApiTest extends TestCase
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

        Schema::create('centro', function (Blueprint $table) {
            $table->increments('idCentro');
        });

        Schema::create('maquinaria', function (Blueprint $table) {
            $table->increments('idMaquinaria');
            $table->string('nombre', 100);
            $table->string('tipo');
            $table->string('estado');
            $table->unsignedInteger('idCentro');
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

        DB::table('centro')->insert([
            ['idCentro' => 1],
            ['idCentro' => 2],
        ]);

        DB::table('maquinaria')->insert([
            'idMaquinaria' => 1,
            'nombre' => 'Bobcat Principal',
            'tipo' => 'Bobcat',
            'estado' => 'Disponible',
            'idCentro' => 1,
        ]);
    }

    private function autenticar(int $id = 1): void
    {
        $this->actingAs(
            Credenciales::findOrFail($id),
            'api'
        );
    }

    private function datosMaquinaria(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Retroexcavadora Nueva',
            'tipo' => 'Retroexcavadora',
            'estado' => 'Disponible',
            'idCentro' => 1,
        ], $cambios);
    }

    public function test_no_autenticado_no_puede_listar_maquinarias(): void
    {
        $this->getJson('/api/maquinarias')
            ->assertUnauthorized();
    }

    public function test_chofer_no_puede_listar_maquinarias(): void
    {
        $this->autenticar(3);

        $this->getJson('/api/maquinarias')
            ->assertForbidden();
    }

    public function test_chofer_no_puede_crear_maquinarias(): void
    {
        $this->autenticar(3);

        $this->postJson(
            '/api/maquinarias',
            $this->datosMaquinaria()
        )->assertForbidden();
    }

    public function test_chofer_no_puede_modificar_maquinarias(): void
    {
        $this->autenticar(3);

        $this->putJson('/api/maquinarias/1', [
            'estado' => 'Inhabilitado',
        ])->assertForbidden();
    }

    public function test_chofer_no_puede_eliminar_maquinarias(): void
    {
        $this->autenticar(3);

        $this->deleteJson('/api/maquinarias/1')
            ->assertForbidden();
    }

    public function test_administrador_puede_listar_maquinarias(): void
    {
        $this->autenticar();

        $this->getJson('/api/maquinarias')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_operario_puede_listar_maquinarias(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/maquinarias')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_administrador_puede_crear_maquinaria(): void
    {
        $this->autenticar();

        $this->postJson(
            '/api/maquinarias',
            $this->datosMaquinaria()
        )
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('maquinaria', [
            'nombre' => 'Retroexcavadora Nueva',
            'tipo' => 'Retroexcavadora',
            'estado' => 'Disponible',
            'idCentro' => 1,
        ]);
    }

    public function test_operario_puede_crear_maquinaria(): void
    {
        $this->autenticar(2);

        $this->postJson(
            '/api/maquinarias',
            $this->datosMaquinaria()
        )->assertCreated();
    }

    public function test_permite_crear_los_tres_tipos_de_maquinaria(): void
    {
        $this->autenticar();

        foreach (['Aplanadora', 'Bobcat', 'Retroexcavadora'] as $tipo) {
            $this->postJson('/api/maquinarias', $this->datosMaquinaria([
                'nombre' => 'Equipo ' . $tipo,
                'tipo' => $tipo,
            ]))->assertCreated();
        }
    }

    public function test_permite_estado_inhabilitado(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', $this->datosMaquinaria([
            'estado' => 'Inhabilitado',
        ]))->assertCreated();

        $this->assertDatabaseHas('maquinaria', [
            'nombre' => 'Retroexcavadora Nueva',
            'estado' => 'Inhabilitado',
        ]);
    }

    public function test_rechaza_tipo_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', $this->datosMaquinaria([
            'tipo' => 'Grua',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_rechaza_estado_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', $this->datosMaquinaria([
            'estado' => 'En mantenimiento',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_rechaza_centro_inexistente(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', $this->datosMaquinaria([
            'idCentro' => 999,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idCentro');
    }

    public function test_rechaza_nombre_mayor_a_sesenta_caracteres_al_crear(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', $this->datosMaquinaria([
            'nombre' => str_repeat('A', 61),
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_rechaza_nombre_vacio(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', $this->datosMaquinaria([
            'nombre' => '',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_valida_campos_obligatorios(): void
    {
        $this->autenticar();

        $this->postJson('/api/maquinarias', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'nombre',
                'tipo',
                'estado',
                'idCentro',
            ]);
    }

    public function test_administrador_puede_consultar_maquinaria(): void
    {
        $this->autenticar();

        $this->getJson('/api/maquinarias/1')
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Bobcat Principal');
    }

    public function test_operario_puede_consultar_maquinaria(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/maquinarias/1')
            ->assertOk();
    }

    public function test_consultar_maquinaria_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/maquinarias/999')
            ->assertNotFound();
    }

    public function test_administrador_puede_actualizar_maquinaria(): void
    {
        $this->autenticar();

        $this->putJson('/api/maquinarias/1', [
            'nombre' => 'Bobcat Actualizada',
            'estado' => 'Inhabilitado',
        ])->assertOk();

        $this->assertDatabaseHas('maquinaria', [
            'idMaquinaria' => 1,
            'nombre' => 'Bobcat Actualizada',
            'estado' => 'Inhabilitado',
        ]);
    }

    public function test_operario_puede_actualizar_maquinaria(): void
    {
        $this->autenticar(2);

        $this->putJson('/api/maquinarias/1', [
            'estado' => 'Inhabilitado',
        ])->assertOk();
    }

    public function test_permite_cambiar_maquinaria_a_otro_centro_existente(): void
    {
        $this->autenticar();

        $this->putJson('/api/maquinarias/1', [
            'idCentro' => 2,
        ])->assertOk();

        $this->assertDatabaseHas('maquinaria', [
            'idMaquinaria' => 1,
            'idCentro' => 2,
        ]);
    }

    public function test_actualizacion_rechaza_centro_inexistente(): void
    {
        $this->autenticar();

        $this->putJson('/api/maquinarias/1', [
            'idCentro' => 999,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idCentro');
    }

    public function test_actualizacion_rechaza_tipo_invalido(): void
    {
        $this->autenticar();

        $this->putJson('/api/maquinarias/1', [
            'tipo' => 'Tractor',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_actualizacion_rechaza_nombre_mayor_a_cien_caracteres(): void
    {
        $this->autenticar();

        $this->putJson('/api/maquinarias/1', [
            'nombre' => str_repeat('A', 101),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_actualizar_maquinaria_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->putJson('/api/maquinarias/999', [
            'estado' => 'Disponible',
        ])->assertNotFound();
    }

    public function test_administrador_puede_eliminar_maquinaria(): void
    {
        $this->autenticar();

        $this->deleteJson('/api/maquinarias/1')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('maquinaria', [
            'idMaquinaria' => 1,
        ]);
    }

    public function test_operario_puede_eliminar_maquinaria(): void
    {
        $this->autenticar(2);

        $this->deleteJson('/api/maquinarias/1')
            ->assertOk();

        $this->assertDatabaseMissing('maquinaria', [
            'idMaquinaria' => 1,
        ]);
    }

    public function test_eliminar_maquinaria_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->deleteJson('/api/maquinarias/999')
            ->assertNotFound();
    }
}
