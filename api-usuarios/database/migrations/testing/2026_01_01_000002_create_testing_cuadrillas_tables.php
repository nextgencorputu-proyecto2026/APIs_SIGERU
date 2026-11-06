
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up(): void
    {
        $this->verificarEntorno();

        Schema::create('vehiculos', function (Blueprint $table) {
            $table->increments('idVehiculo');
            $table->string('matricula')->unique();
            $table->string('marca')->nullable();
            $table->string('tipo');
            $table->string('estado');
        });

        Schema::create('ruta', function (Blueprint $table) {
            $table->increments('idRuta');
            $table->string('tipoResiduo');
        });

        Schema::create('cuadrilla', function (Blueprint $table) {
            $table->increments('idCuadrilla');
        });

        Schema::create('integra_cuadrilla', function (Blueprint $table) {
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idUsu');
            $table->boolean('esResponsable')->default(false);

            $table->primary(['idCuadrilla', 'idUsu']);

            $table->foreign('idCuadrilla')
                ->references('idCuadrilla')
                ->on('cuadrilla')
                ->cascadeOnDelete();

            $table->foreign('idUsu')
                ->references('idUsu')
                ->on('usuario');
        });

        Schema::create('cuadrilla_vehiculo', function (Blueprint $table) {
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idVehiculo');

            $table->primary(['idCuadrilla', 'idVehiculo']);

            $table->foreign('idCuadrilla')
                ->references('idCuadrilla')
                ->on('cuadrilla')
                ->cascadeOnDelete();

            $table->foreign('idVehiculo')
                ->references('idVehiculo')
                ->on('vehiculos');
        });

        Schema::create('asigna_ruta', function (Blueprint $table) {
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idRuta');
            $table->unsignedInteger('idUsu');

            $table->primary(['idCuadrilla', 'idRuta']);

            $table->foreign('idCuadrilla')
                ->references('idCuadrilla')
                ->on('cuadrilla')
                ->cascadeOnDelete();

            $table->foreign('idRuta')
                ->references('idRuta')
                ->on('ruta');

            $table->foreign('idUsu')
                ->references('idUsu')
                ->on('usuario');
        });

        foreach ([
            'atiende',
            'cumple_recoleccion',
            'cumple_barredora',
        ] as $nombre) {
            Schema::create($nombre, function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('idCuadrilla');
                $table->dateTime('fechaHoraFin')->nullable();

                $table->foreign('idCuadrilla')
                    ->references('idCuadrilla')
                    ->on('cuadrilla');
            });
        }
    }

    public function down(): void
    {
        $this->verificarEntorno();

        Schema::dropIfExists('cumple_barredora');
        Schema::dropIfExists('cumple_recoleccion');
        Schema::dropIfExists('atiende');
        Schema::dropIfExists('asigna_ruta');
        Schema::dropIfExists('cuadrilla_vehiculo');
        Schema::dropIfExists('integra_cuadrilla');
        Schema::dropIfExists('cuadrilla');
        Schema::dropIfExists('ruta');
        Schema::dropIfExists('vehiculos');
    }

    private function verificarEntorno(): void
    {
        if (
            !app()->environment('testing') ||
            DB::getDefaultConnection() !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException(
                'Esta migración solo puede ejecutarse en testing con SQLite en memoria.'
            );
        }
    }
};
