<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuarioApiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('centro', function (Blueprint $table) {
            $table->increments('idCentro');
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('idUsu');
            $table->string('ci')->unique();
            $table->string('nombre1', 50);
            $table->string('nombre2', 50)->nullable();
            $table->string('apellido1', 50);
            $table->string('apellido2', 50)->nullable();
            $table->date('fec_nac');
            $table->string('tipo');
            $table->unsignedInteger('idCentro');
        });

        Schema::create('credenciales', function (Blueprint $table) {
            $table->unsignedInteger('idUsu')->primary();
            $table->string('mail')->unique();
            $table->string('contrasena');
        });

        DB::table('centro')->insert([
            'idCentro' => 1,
        ]);

        DB::table('usuario')->insert([
            [
                'idUsu' => 1,
                'ci' => '12345678',
                'nombre1' => 'Administrador',
                'nombre2' => null,
                'apellido1' => 'Principal',
                'apellido2' => null,
                'fec_nac' => '1990-01-01',
                'tipo' => 'Administrador',
                'idCentro' => 1,
            ],
            [
                'idUsu' => 2,
                'ci' => '87654321',
                'nombre1' => 'Pedro',
                'nombre2' => null,
                'apellido1' => 'Gomez',
                'apellido2' => null,
                'fec_nac' => '1995-05-10',
                'tipo' => 'Operario',
                'idCentro' => 1,
            ],
        ]);

        DB::table('credenciales')->insert([
            [
                'idUsu' => 1,
                'mail' => 'admin@sigeru.test',
                'contrasena' => Hash::make('ClaveAdmin123'),
            ],
            [
                'idUsu' => 2,
                'mail' => 'pedro@sigeru.test',
                'contrasena' => Hash::make('ClavePedro123'),
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

    private function datosUsuario(array $cambios = []): array
    {
        return array_merge([
            'ci' => '11223344',
            'nombre1' => 'Maria',
            'apellido1' => 'Lopez',
            'fec_nac' => '1998-04-15',
            'tipo' => 'Operario',
            'idCentro' => 1,
            'email' => 'maria@sigeru.test',
            'password' => 'ClaveMaria123',
        ], $cambios);
    }

    public function test_no_autenticado_no_puede_listar_usuarios(): void
    {
        $this->getJson('/api/usuarios')
            ->assertUnauthorized();
    }

    public function test_operario_no_puede_listar_usuarios(): void
    {
        $this->autenticar(2);

        $this->getJson('/api/usuarios')
            ->assertForbidden();
    }

    public function test_operario_no_puede_crear_usuarios(): void
    {
        $this->autenticar(2);

        $this->postJson(
            '/api/usuarios',
            $this->datosUsuario()
        )->assertForbidden();
    }

    public function test_operario_no_puede_modificar_usuarios(): void
    {
        $this->autenticar(2);

        $this->putJson('/api/usuarios/1', [
            'nombre1' => 'Cambio',
        ])->assertForbidden();
    }

    public function test_administrador_puede_listar_usuarios(): void
    {
        $this->autenticar();

        $this->getJson('/api/usuarios')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_administrador_puede_buscar_por_nombre(): void
    {
        $this->autenticar();

        $this->getJson('/api/usuarios?nombre=Pedro')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre1', 'Pedro');
    }

    public function test_administrador_puede_crear_usuario(): void
    {
        $this->autenticar();

        $this->postJson(
            '/api/usuarios',
            $this->datosUsuario()
        )
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('usuario', [
            'ci' => '11223344',
            'nombre1' => 'Maria',
        ]);

        $this->assertDatabaseHas('credenciales', [
            'mail' => 'maria@sigeru.test',
        ]);
    }

    public function test_contrasena_se_guarda_encriptada(): void
    {
        $this->autenticar();

        $this->postJson(
            '/api/usuarios',
            $this->datosUsuario()
        )->assertCreated();

        $hash = DB::table('credenciales')
            ->where('mail', 'maria@sigeru.test')
            ->value('contrasena');

        $this->assertNotEquals('ClaveMaria123', $hash);

        $this->assertTrue(
            Hash::check('ClaveMaria123', $hash)
        );
    }

    public function test_no_permite_ci_duplicada(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'ci' => '12345678',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ci');
    }

    public function test_no_permite_email_duplicado(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'email' => 'pedro@sigeru.test',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function test_no_permite_ci_con_longitud_incorrecta(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'ci' => '123',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ci');
    }

    public function test_no_permite_rol_invalido(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'tipo' => 'Vecino',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_no_permite_centro_inexistente(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'idCentro' => 999,
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idCentro');
    }

    public function test_no_permite_contrasena_corta(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'password' => 'Ab1',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_contrasena_requiere_mayuscula(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', $this->datosUsuario([
            'password' => 'clave12345',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('password');
    }

    public function test_campos_obligatorios_son_validados(): void
    {
        $this->autenticar();

        $this->postJson('/api/usuarios', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'ci',
                'nombre1',
                'apellido1',
                'fec_nac',
                'tipo',
                'idCentro',
                'email',
                'password',
            ]);
    }

    public function test_administrador_puede_consultar_usuario(): void
    {
        $this->autenticar();

        $this->getJson('/api/usuarios/2')
            ->assertOk()
            ->assertJsonPath('data.nombre1', 'Pedro');
    }

    public function test_consultar_usuario_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->getJson('/api/usuarios/999')
            ->assertNotFound();
    }

    public function test_administrador_puede_actualizar_usuario(): void
    {
        $this->autenticar();

        $this->putJson('/api/usuarios/2', [
            'nombre1' => 'Pablo',
            'apellido1' => 'Gomez',
        ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('usuario', [
            'idUsu' => 2,
            'nombre1' => 'Pablo',
        ]);
    }

    public function test_actualizar_usuario_inexistente_devuelve_404(): void
    {
        $this->autenticar();

        $this->putJson('/api/usuarios/999', [
            'nombre1' => 'Prueba',
        ])->assertNotFound();
    }

    public function test_actualizacion_rechaza_rol_invalido(): void
    {
        $this->autenticar();

        $this->putJson('/api/usuarios/2', [
            'tipo' => 'Vecino',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_actualizacion_rechaza_ci_duplicada(): void
    {
        $this->autenticar();

        $this->putJson('/api/usuarios/2', [
            'ci' => '12345678',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('ci');
    }
}
