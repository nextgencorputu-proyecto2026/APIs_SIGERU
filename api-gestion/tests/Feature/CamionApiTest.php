<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Concerns\RefreshSigeruDatabase;
use App\Models\Usuario;
use App\Models\Credenciales;
use Illuminate\Support\Facades\DB;

class CamionApiTest extends TestCase
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

    private function datosCamion(): array
    {
        return [
            'matricula' => 'STP1234',
            'marca' => 'Mercedes-Benz',
            'tipo' => 'Camioneta',
            'estado' => 'Disponible',
        ];
    }

    public function test_administrador_puede_listar_camiones(): void
    {
        $this->autenticarComo('Administrador');

        $this->getJson('/api/camiones')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_administrador_puede_crear_camion(): void
    {
        $this->autenticarComo('Administrador');

        $this->postJson('/api/camiones', $this->datosCamion())
            ->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('vehiculos', [
            'matricula' => 'STP1234',
        ]);
    }

    public function test_no_permite_matricula_duplicada(): void
    {
        $this->autenticarComo('Administrador');

        $this->postJson('/api/camiones', $this->datosCamion())
            ->assertCreated();

        $this->postJson('/api/camiones', $this->datosCamion())
            ->assertUnprocessable()
            ->assertJsonValidationErrors('matricula');
    }

    public function test_camion_recolector_requiere_capacidad(): void
    {
        $this->autenticarComo('Administrador');

        $datos = $this->datosCamion();
        $datos['tipo'] = 'Camión recolector mixtos';

        $this->postJson('/api/camiones', $datos)
            ->assertUnprocessable();
    }

    public function test_administrador_puede_consultar_camion(): void
    {
        $this->autenticarComo('Administrador');

        $id = DB::table('vehiculos')->insertGetId(
            $this->datosCamion()
        );

        $this->getJson("/api/camiones/{$id}")
            ->assertOk()
            ->assertJsonPath('data.matricula', 'STP1234');
    }

    public function test_administrador_puede_actualizar_camion(): void
    {
        $this->autenticarComo('Administrador');

        $id = DB::table('vehiculos')->insertGetId(
            $this->datosCamion()
        );

        $this->putJson("/api/camiones/{$id}", [
            'marca' => 'Volvo',
        ])
            ->assertOk()
            ->assertJsonPath('data.marca', 'Volvo');
    }

    public function test_administrador_puede_eliminar_camion(): void
    {
        $this->autenticarComo('Administrador');

        $id = DB::table('vehiculos')->insertGetId(
            $this->datosCamion()
        );

        $this->deleteJson("/api/camiones/{$id}")
            ->assertOk();

        $this->assertDatabaseMissing('vehiculos', [
            'idVehiculo' => $id,
        ]);
    }

    public function test_operario_no_puede_crear_camion(): void
    {
        $this->autenticarComo('Operario');

        $this->postJson('/api/camiones', $this->datosCamion())
            ->assertForbidden();
    }

    public function test_usuario_no_autenticado_no_puede_listar_camiones(): void
    {
        $this->getJson('/api/camiones')
            ->assertUnauthorized();
    }

    public function test_camion_inexistente_devuelve_404(): void
    {
        $this->autenticarComo('Administrador');

        $this->getJson('/api/camiones/99999')
            ->assertNotFound();
    }
}
