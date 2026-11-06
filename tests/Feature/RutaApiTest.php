<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RutaApiTest extends TestCase
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
            $table->string('descripcion');
            $table->string('horario');
            $table->string('tipoResiduo');
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
            [
                'idRuta' => 1,
                'descripcion' => 'Ruta Centro',
                'horario' => '08:00',
                'tipoResiduo' => 'Mixtos',
            ],
            [
                'idRuta' => 2,
                'descripcion' => 'Ruta Buceo',
                'horario' => '09:00',
                'tipoResiduo' => 'Reciclables',
            ],
            [
                'idRuta' => 3,
                'descripcion' => 'Ruta Pocitos',
                'horario' => '10:00',
                'tipoResiduo' => 'Mixtos',
            ],
            [
                'idRuta' => 4,
                'descripcion' => 'Ruta Malvin',
                'horario' => '11:00',
                'tipoResiduo' => 'Reciclables',
            ],
        ]);
    }

    private function autenticar(int $id = 1): void
    {
        $this->actingAs(
            Credenciales::findOrFail($id),
            'api'
        );
    }

    public function test_no_autenticado_no_puede_listar_rutas(): void
    {
        $this->getJson('/api/rutas')
            ->assertUnauthorized();
    }

    public function test_administrador_puede_listar_rutas(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_operario_puede_listar_rutas(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/rutas')
            ->assertOk();
    }

    public function test_chofer_no_puede_listar_rutas(): void
    {
        $this->autenticar(3);

        $this->getJson('/api/rutas')
            ->assertForbidden();
    }

    public function test_lista_todas_las_rutas_sin_filtro(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_filtra_rutas_de_residuos_mixtos(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas?tipoResiduo=Mixtos')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.tipoResiduo', 'Mixtos')
            ->assertJsonPath('data.1.tipoResiduo', 'Mixtos');
    }

    public function test_filtra_rutas_de_residuos_reciclables(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas?tipoResiduo=Reciclables')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.tipoResiduo', 'Reciclables')
            ->assertJsonPath('data.1.tipoResiduo', 'Reciclables');
    }

    public function test_rechaza_tipo_de_residuo_invalido(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas?tipoResiduo=Organicos')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipoResiduo');
    }

    public function test_rechaza_tipo_de_residuo_con_mayusculas_incorrectas(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas?tipoResiduo=mixtos')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipoResiduo');
    }

    public function test_rutas_se_ordenan_por_descripcion(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas')
            ->assertOk()
            ->assertJsonPath('data.0.descripcion', 'Ruta Buceo')
            ->assertJsonPath('data.1.descripcion', 'Ruta Centro')
            ->assertJsonPath('data.2.descripcion', 'Ruta Malvin')
            ->assertJsonPath('data.3.descripcion', 'Ruta Pocitos');
    }

    public function test_respuesta_contiene_los_campos_correctos(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'idRuta',
                        'descripcion',
                        'horario',
                        'tipoResiduo',
                    ],
                ],
            ]);
    }

    public function test_filtro_no_modifica_las_rutas_de_la_base(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas?tipoResiduo=Mixtos')
            ->assertOk();

        $this->assertDatabaseCount('ruta', 4);
    }

    public function test_permite_consultar_sin_filtro_despues_de_filtrar(): void
    {
        $this->autenticar();

        $this->getJson('/api/rutas?tipoResiduo=Reciclables')
            ->assertJsonCount(2, 'data');

        $this->getJson('/api/rutas')
            ->assertOk()
            ->assertJsonCount(4, 'data');
    }

    public function test_devuelve_lista_vacia_si_no_hay_rutas(): void
    {
        $this->autenticar();

        DB::table('ruta')->delete();

        $this->getJson('/api/rutas')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }

    public function test_devuelve_lista_vacia_si_no_hay_rutas_del_tipo_solicitado(): void
    {
        $this->autenticar();

        DB::table('ruta')
            ->where('tipoResiduo', 'Reciclables')
            ->delete();

        $this->getJson('/api/rutas?tipoResiduo=Reciclables')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }
}
