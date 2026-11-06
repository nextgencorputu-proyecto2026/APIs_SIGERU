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

        Schema::create('incidencia', function (Blueprint $table) {
            $table->increments('idIncidencia');
            $table->string('tipo');
            $table->enum('prioridad', ['Alta', 'Media', 'Baja']);
            $table->enum('estado', ['Pendiente', 'En curso', 'Resuelta'])
                ->default('Pendiente');
            $table->dateTime('fechaCreacion');
            $table->dateTime('fechaResolucion')->nullable();
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

        Schema::dropIfExists('incidencia');
    }
};
