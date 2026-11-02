<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\Concerns\RefreshSigeruDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseTestingTest extends TestCase
{
    use RefreshSigeruDatabase;

    public function test_utiliza_sqlite_en_memoria(): void
    {
        $this->assertEquals('testing', app()->environment());

        $this->assertEquals(
            'sqlite',
            DB::getDefaultConnection()
        );

        $this->assertEquals(
            ':memory:',
            DB::connection()->getDatabaseName()
        );
    }

    public function test_tablas_de_usuarios_existen(): void
    {
        foreach ([
            'centro',
            'usuario',
            'credenciales',
        ] as $tabla) {
            $this->assertTrue(
                Schema::hasTable($tabla),
                "No existe la tabla {$tabla}"
            );
        }
    }

    public function test_tablas_de_cuadrillas_existen(): void
    {
        foreach ([
            'vehiculos',
            'ruta',
            'cuadrilla',
            'integra_cuadrilla',
            'cuadrilla_vehiculo',
            'asigna_ruta',
            'atiende',
            'cumple_recoleccion',
            'cumple_barredora',
        ] as $tabla) {
            $this->assertTrue(
                Schema::hasTable($tabla),
                "No existe la tabla {$tabla}"
            );
        }
    }
}
