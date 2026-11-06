<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\RefreshSigeruDatabase;
use Tests\TestCase;

class MaquinariaApiTest extends TestCase
{
    use RefreshSigeruDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refreshSigeruDatabase();
    }

    private function autenticarComo(string $rol): void
    {
        $usuario = Usuario::create([
            'ci' => '12345678',
            'nombre1' => 'Usuario',
            'apellido1' => 'Prueba',
            'tipo' => $rol,
        ]);

        $credencial = Credenciales::create([
            'idUsu' => $usuario->idUsu,
            'mail' => 'prueba@sigeru.test',
            'contrasena' => bcrypt('12345678'),
        ]);

        $this->actingAs($credencial, 'api');
    }

    private function crearCentro(): int
    {
        return DB::table('centro')->insertGetId([
            'nombre' => 'Centro Buceo',
            'direccion' => 'Avenida Italia 3500',
            'capacidad' => 1500,
            'tipo' => 'Centro de acopio',
            'ubicacionX' => -34.9000,
            'ubicacionY' => -56.1200,
        ]);
    }

    private function datosMaquinaria(int $idCentro): array
    {
        return [
            'nombre' => 'Retroexcavadora de prueba',
            'tipo' => 'Retroexcavadora',
            'estado' => 'Disponible',
            'idCentro' => $idCentro,
        ];
    }

    private function crearMaquinaria(int $idCentro): int
    {
        return DB::table('maquinaria')->insertGetId(
            $this->datosMaquinaria($idCentro)
        );
    }

    public function test_administrador_puede_listar_maquinarias(): void
    {
        $this->autenticarComo('Administrador');

        $this->getJson('/api/maquinarias')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_administrador_puede_crear_maquinaria(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();

        $this->postJson(
            '/api/maquinarias',
            $this->datosMaquinaria($idCentro)
        )
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('maquinaria', [
            'nombre' => 'Retroexcavadora de prueba',
            'idCentro' => $idCentro,
        ]);
    }

    public function test_operario_puede_crear_maquinaria(): void
    {
        $this->autenticarComo('Operario');

        $idCentro = $this->crearCentro();

        $this->postJson(
            '/api/maquinarias',
            $this->datosMaquinaria($idCentro)
        )->assertCreated();
    }

    public function test_maquinaria_requiere_nombre(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();
        $datos = $this->datosMaquinaria($idCentro);

        unset($datos['nombre']);

        $this->postJson('/api/maquinarias', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_maquinaria_rechaza_tipo_invalido(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();
        $datos = $this->datosMaquinaria($idCentro);
        $datos['tipo'] = 'Grua';

        $this->postJson('/api/maquinarias', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('tipo');
    }

    public function test_maquinaria_rechaza_centro_inexistente(): void
    {
        $this->autenticarComo('Administrador');

        $this->postJson(
            '/api/maquinarias',
            $this->datosMaquinaria(99999)
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idCentro');
    }

    public function test_administrador_puede_consultar_maquinaria(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();
        $id = $this->crearMaquinaria($idCentro);

        $this->getJson("/api/maquinarias/{$id}")
            ->assertOk()
            ->assertJsonPath(
                'data.nombre',
                'Retroexcavadora de prueba'
            );
    }

    public function test_administrador_puede_actualizar_maquinaria(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();
        $id = $this->crearMaquinaria($idCentro);

        $this->putJson("/api/maquinarias/{$id}", [
            'estado' => 'Inhabilitado',
        ])
            ->assertOk()
            ->assertJsonPath('data.estado', 'Inhabilitado');
    }

    public function test_administrador_puede_eliminar_maquinaria(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();
        $id = $this->crearMaquinaria($idCentro);

        $this->deleteJson("/api/maquinarias/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('maquinaria', [
            'idMaquinaria' => $id,
        ]);
    }

    public function test_usuario_no_autenticado_no_puede_listar_maquinarias(): void
    {
        $this->getJson('/api/maquinarias')
            ->assertUnauthorized();
    }

    public function test_maquinaria_inexistente_devuelve_404(): void
    {
        $this->autenticarComo('Administrador');

        $this->getJson('/api/maquinarias/99999')
            ->assertNotFound();
    }

    public function test_no_se_puede_eliminar_centro_con_maquinaria_asociada(): void
    {
        $this->autenticarComo('Administrador');

        $idCentro = $this->crearCentro();
        $this->crearMaquinaria($idCentro);

        $this->deleteJson("/api/centros-acopio/{$idCentro}")
            ->assertStatus(409);

        $this->assertDatabaseHas('centro', [
            'idCentro' => $idCentro,
        ]);
    }
}
