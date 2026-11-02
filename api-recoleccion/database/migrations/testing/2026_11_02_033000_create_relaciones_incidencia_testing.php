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

        foreach ([
            'vehiculo_presenta',
            'reporta',
            'controla',
        ] as $tabla) {
            Schema::create($tabla, function (Blueprint $table) {
                $table->unsignedInteger('idIncidencia');
                $table->unsignedInteger('idUsu')->nullable();
                $table->unsignedInteger('idVehiculo')->nullable();
            });
        }

        Schema::create('atiende', function (Blueprint $table) {
            $table->increments('idAtencion');
            $table->unsignedInteger('idIncidencia');
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idVehiculo');
            $table->dateTime('fechaHoraIni');
            $table->dateTime('fechaHoraFin')->nullable();
        });
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

        Schema::dropIfExists('atiende');
        Schema::dropIfExists('controla');
        Schema::dropIfExists('reporta');
        Schema::dropIfExists('vehiculo_presenta');
    }
};
