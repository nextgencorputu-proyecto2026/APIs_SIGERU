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
            throw new RuntimeException(
                'Esta migración requiere SQLite en memoria para Testing.'
            );
        }

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('idUsu');
            $table->char('ci', 8)->unique();
            $table->string('nombre1', 30);
            $table->string('nombre2', 30)->nullable();
            $table->string('apellido1', 30);
            $table->string('apellido2', 30)->nullable();
            $table->date('fec_nac');
            $table->enum('tipo', [
                'Administrador',
                'Operario',
                'Chofer'
            ]);
            $table->unsignedInteger('idCentro');
        });
    }

    public function down(): void
    {
        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException(
                'Esta migración requiere SQLite en memoria para Testing.'
            );
        }

        Schema::dropIfExists('usuario');
    }
};
