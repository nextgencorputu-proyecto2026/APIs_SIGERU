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

        Schema::create('maquinaria', function (Blueprint $table) {
            $table->increments('idMaquinaria');
            $table->string('nombre', 100);
            $table->string('tipo', 30);
            $table->string('estado', 30);
            $table->unsignedInteger('idCentro');

            $table->foreign('idCentro')
                ->references('idCentro')
                ->on('centro')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        $this->verificarSQLite();

        Schema::dropIfExists('maquinaria');
    }
};
