<?php

namespace Tests\Feature;

use App\Models\Credenciales;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\RefreshSigeruDatabase;
use Tests\TestCase;

class CentroAcopioApiTest extends TestCase
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

    private function datosCentro(): array
    {
        return [
            'nombre' => 'Centro Buceo',
            'direccion' => 'Avenida Italia 3500',
            'capacidad' => 1500,
            'tipo' => 'Centro de acopio',
            'ubicacionX' => -34.9000,
            'ubicacionY' => -56.1200,
        ];
    }

    private function crearCentro(): int
    {
        return DB::table('centro')->insertGetId(
            $this->datosCentro()
        );
    }

    public function test_administrador_puede_listar_centros(): void
    {
        $this->autenticarComo('Administrador');

        $this->getJson('/api/centros-acopio')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_administrador_puede_crear_centro(): void
    {
        $this->autenticarComo('Administrador');

        $this->postJson('/api/centros-acopio', $this->datosCentro())
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('centro', [
            'nombre' => 'Centro Buceo',
        ]);
    }

    public function test_centro_requiere_nombre(): void
    {
        $this->autenticarComo('Administrador');

        $datos = $this->datosCentro();
        unset($datos['nombre']);

        $this->postJson('/api/centros-acopio', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('nombre');
    }

    public function test_centro_requiere_coordenadas(): void
    {
        $this->autenticarComo('Administrador');

        $datos = $this->datosCentro();
        unset($datos['ubicacionX'], $datos['ubicacionY']);

        $this->postJson('/api/centros-acopio', $datos)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'ubicacionX',
                'ubicacionY',
            ]);
    }

    public function test_centro_acopio_requiere_capacidad(): void
    {
        $this->autenticarComo('Administrador');

        $datos = $this->datosCentro();
        unset($datos['capacidad']);

        $this->postJson('/api/centros-acopio', $datos)
            ->assertUnprocessable();
    }

    public function test_central_operativa_no_guarda_capacidad(): void
    {
        $this->autenticarComo('Administrador');

        $datos = $this->datosCentro();
        $datos['tipo'] = 'Central Operativa';

        $this->postJson('/api/centros-acopio', $datos)
            ->assertCreated();

        $this->assertDatabaseHas('centro', [
            'nombre' => 'Centro Buceo',
            'tipo' => 'Central Operativa',
            'capacidad' => null,
        ]);
    }

    public function test_administrador_puede_consultar_centro(): void
    {
        $this->autenticarComo('Administrador');

        $id = $this->crearCentro();

        $this->getJson("/api/centros-acopio/{$id}")
            ->assertOk()
            ->assertJsonPath('data.nombre', 'Centro Buceo');
    }

    public function test_administrador_puede_actualizar_centro(): void
    {
        $this->autenticarComo('Administrador');

        $id = $this->crearCentro();

        $this->putJson("/api/centros-acopio/{$id}", [
            'nombre' => 'Centro Buceo Actualizado',
        ])
            ->assertOk()
            ->assertJsonPath(
                'data.nombre',
                'Centro Buceo Actualizado'
            );
    }

    public function test_administrador_puede_eliminar_centro(): void
    {
        $this->autenticarComo('Administrador');

        $id = $this->crearCentro();

        $this->deleteJson("/api/centros-acopio/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('centro', [
            'idCentro' => $id,
        ]);
    }

    public function test_operario_no_puede_crear_centro(): void
    {
        $this->autenticarComo('Operario');

        $this->postJson('/api/centros-acopio', $this->datosCentro())
            ->assertForbidden();
    }

    public function test_usuario_no_autenticado_no_puede_listar_centros(): void
    {
        $this->getJson('/api/centros-acopio')
            ->assertUnauthorized();
    }

    public function test_centro_inexistente_devuelve_404(): void
    {
        $this->autenticarComo('Administrador');

        $this->getJson('/api/centros-acopio/99999')
            ->assertNotFound();
    }
}
