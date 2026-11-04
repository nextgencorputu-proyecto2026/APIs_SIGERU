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

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('idUsu');
            $table->string('ci', 20)->unique();
            $table->string('nombre1', 50);
            $table->string('nombre2', 50)->nullable();
            $table->string('apellido1', 50);
            $table->string('apellido2', 50)->nullable();
            $table->date('fec_nac')->nullable();
            $table->string('tipo', 30);
            $table->unsignedInteger('idCentro')->nullable();
        });

        Schema::create('credenciales', function (Blueprint $table) {
            $table->unsignedInteger('idUsu')->primary();
            $table->string('mail', 100)->unique();
            $table->string('contrasena');

            $table->foreign('idUsu')
                ->references('idUsu')
                ->on('usuario')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $this->verificarSQLite();

        Schema::dropIfExists('credenciales');
        Schema::dropIfExists('usuario');
    }
};
