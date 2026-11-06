<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private function verificarSQLite(): void
    {
        if (
            DB::getDriverName() !== 'sqlite' ||
            DB::connection()->getDatabaseName() !== ':memory:'
        ) {
            throw new RuntimeException(
                'Esta migración solo puede ejecutarse en SQLite en memoria.'
            );
        }
    }

    public function up(): void
    {
        $this->verificarSQLite();

        Schema::create('centro', function (Blueprint $table) {
            $table->increments('idCentro');
            $table->string('nombre', 60);
            $table->string('direccion', 120);
            $table->decimal('capacidad', 10, 2)->nullable();
            $table->string('tipo', 40);
            $table->decimal('ubicacionX', 8, 4);
            $table->decimal('ubicacionY', 8, 4);
        });
    }

    public function down(): void
    {
        $this->verificarSQLite();

        Schema::dropIfExists('centro');
    }
};
