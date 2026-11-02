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

        Schema::create('credenciales', function (Blueprint $table) {
            $table->unsignedInteger('idUsu')->primary();
            $table->string('mail', 255)->unique();
            $table->string('contrasena', 255);

            $table->foreign('idUsu')
                ->references('idUsu')
                ->on('usuario')
                ->cascadeOnDelete();
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

        Schema::dropIfExists('credenciales');
    }
};
