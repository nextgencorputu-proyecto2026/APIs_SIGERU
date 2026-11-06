<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ContenedorApiTest extends TestCase
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

        Schema::create('ruta', function (Blueprint $table) {
            $table->increments('idRuta');
            $table->string('tipoResiduo');
        });

        Schema::create('contenedor', function (Blueprint $table) {
            $table->increments('idContenedor');
            $table->decimal('ubicacionX', 10, 4);
            $table->decimal('ubicacionY', 10, 4);
            $table->string('estado');
            $table->decimal('nivelLlenado', 5, 2);
            $table->string('tipo');
            $table->unsignedInteger('idRuta')->nullable();
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

        DB::table('ruta')->insert([
            ['idRuta' => 1, 'tipoResiduo' => 'Mixtos'],
            ['idRuta' => 2, 'tipoResiduo' => 'Reciclables'],
        ]);

        DB::table('contenedor')->insert([
            'idContenedor' => 1,
            'ubicacionX' => -34.9000,
            'ubicacionY' => -56.1500,
            'estado' => 'Disponible',
            'nivelLlenado' => 20,
            'tipo' => 'Mixtos',
            'idRuta' => 1,
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
            'ubicacionX' => -34.9100,
            'ubicacionY' => -56.1600,
            'estado' => 'Disponible',
            'nivelLlenado' => 0,
            'tipo' => 'Mixtos',
            'idRuta' => 1,
        ], $cambios);
    }

    public function test_no_autenticado_no_puede_listar_contenedores(): void
    {
        $this->getJson('/api/contenedores')
            ->assertUnauthorized();
    }

    public function test_chofer_no_puede_listar_contenedores(): void
    {
        $this->autenticar(3);

        $this->getJson('/api/contenedores')
            ->assertForbidden();
    }

    public function test_chofer_no_puede_crear_contenedores(): void
    {
        $this->autenticar(3);

        $this->postJson(
            '/api/contenedores',
            $this->datosContenedor()
        )->assertForbidden();
    }

    public function test_chofer_no_puede_modificar_contenedores(): void
    {
        $this->autenticar(3);

        $this->putJson('/api/contenedores/1', [
            'estado' => 'Inhabilitado',
        ])->assertForbidden();
    }

    public function test_chofer_no_puede_eliminar_contenedores(): void
    {
        $this->autenticar(3);

        $this->deleteJson('/api/contenedores/1')
            ->assertForbidden();
    }

    public function test_administrador_puede_listar_contenedores(): void
    {
        $this->autenticar();

        $this->getJson('/api/contenedores')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_operario_puede_listar_contenedores(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/contenedores')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_administrador_puede_crear_contenedor(): void
    {
        $this->autenticar();

        $this->postJson(
            '/api/contenedores',
            $this->datosContenedor()
        )->assertCreated();

        $this->assertDatabaseHas('contenedor', [
            'tipo' => 'Mixtos',
            'nivelLlenado' => 0,
            'idRuta' => 1,
        ]);
    }

    public function test_operario_puede_crear_contenedor(): void
    {
        $this->autenticar(2);

        $this->postJson(
            '/api/contenedores',
            $this->datosContenedor()
        )->assertCreated();
    }

    public function test_contenedor_puede_crearse_sin_ruta(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'idRuta' => null,
        ]))->assertCreated();
    }

    public function test_no_permite_ruta_inexistente(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'idRuta' => 999,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idRuta');
    }

    public function test_no_permite_ruta_de_otro_tipo_residuo(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'idRuta' => 2,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idRuta');
    }

    public function test_no_permite_nivel_inicial_distinto_de_cero(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'nivelLlenado' => 50,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nivelLlenado');
    }

    public function test_no_permite_tipo_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'tipo' => 'Organicos',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_no_permite_estado_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'estado' => 'Roto',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('estado');
    }

    public function test_no_permite_coordenadas_fuera_de_rango(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'ubicacionX' => 150,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ubicacionX');
    }

    public function test_no_permite_coordenadas_con_mas_de_cuatro_decimales(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', $this->datosContenedor([
            'ubicacionX' => -34.912345,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ubicacionX');
    }

    public function test_valida_campos_obligatorios(): void
    {
        $this->autenticar();

        $this->postJson('/api/contenedores', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'ubicacionX',
                'ubicacionY',
                'estado',
                'nivelLlenado',
                'tipo',
            ]);
    }

    public function test_administrador_puede_consultar_contenedor(): void
    {
        $this->autenticar();

        $this->getJson('/api/contenedores/1')
            ->assertOk()
            ->assertJsonPath('data.tipo', 'Mixtos');
    }

    public function test_consultar_contenedor_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/contenedores/999')
            ->assertNotFound();
    }

    public function test_operario_puede_actualizar_contenedor(): void
    {
        $this->autenticar(2);

        $this->putJson('/api/contenedores/1', [
            'nivelLlenado' => 75,
        ])->assertOk();

        $this->assertDatabaseHas('contenedor', [
            'idContenedor' => 1,
            'nivelLlenado' => 75,
        ]);
    }

    public function test_actualizacion_rechaza_nivel_mayor_a_cien(): void
    {
        $this->autenticar();

        $this->putJson('/api/contenedores/1', [
            'nivelLlenado' => 120,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nivelLlenado');
    }

    public function test_actualizacion_rechaza_ruta_incompatible(): void
    {
        $this->autenticar();

        $this->putJson('/api/contenedores/1', [
            'idRuta' => 2,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idRuta');
    }

    public function test_actualizacion_permite_cambiar_estado(): void
    {
        $this->autenticar();

        $this->putJson('/api/contenedores/1', [
            'estado' => 'En mantenimiento',
        ])->assertOk();

        $this->assertDatabaseHas('contenedor', [
            'idContenedor' => 1,
            'estado' => 'En mantenimiento',
        ]);
    }

    public function test_actualizar_contenedor_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->putJson('/api/contenedores/999', [
            'estado' => 'Disponible',
        ])->assertNotFound();
    }

    public function test_operario_puede_eliminar_contenedor(): void
    {
        $this->autenticar(2);

        $this->deleteJson('/api/contenedores/1')
            ->assertOk();

        $this->assertDatabaseMissing('contenedor', [
            'idContenedor' => 1,
        ]);
    }

    public function test_eliminar_contenedor_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->deleteJson('/api/contenedores/999')
            ->assertNotFound();
    }

    public function test_no_permite_cambiar_tipo_si_la_ruta_queda_incompatible(): void
    {
        $this->autenticar();

        $this->putJson('/api/contenedores/1', [
            'tipo' => 'Reciclables',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');

        $this->assertDatabaseHas('contenedor', [
            'idContenedor' => 1,
            'tipo' => 'Mixtos',
            'idRuta' => 1,
        ]);
    }

}

