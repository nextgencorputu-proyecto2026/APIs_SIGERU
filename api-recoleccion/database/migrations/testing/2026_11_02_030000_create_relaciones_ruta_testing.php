<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException('Esta migración es solo para SQLite de testing.');
        }

        $tablas = [
            'asigna_ruta',
            'cumple_barredora',
            'cumple_recoleccion',
            'se_realiza_en',
        ];

        foreach ($tablas as $tabla) {
            Schema::create($tabla, function (Blueprint $table) use ($tabla) {
                $table->id();
                $table->unsignedInteger('idRuta');

                if ($tabla === 'asigna_ruta') {
                    $table->unsignedInteger('idCuadrilla');
                }
            });
        }
    }

    public function down(): void
    {
        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException('Esta migración es solo para SQLite de testing.');
        }

        foreach ([
            'asigna_ruta',
            'cumple_barredora',
            'cumple_recoleccion',
            'se_realiza_en',
        ] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
