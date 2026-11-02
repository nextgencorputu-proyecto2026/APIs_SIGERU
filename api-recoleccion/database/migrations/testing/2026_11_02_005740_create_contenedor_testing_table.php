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
            throw new RuntimeException('Esta migración requiere SQLite en memoria para Testing.');
        }

        Schema::create('CONTENEDOR', function (Blueprint $table) {
            $table->increments('idContenedor');
            $table->decimal('ubicacionX', 6, 4);
            $table->decimal('ubicacionY', 6, 4);
            $table->enum('estado', [
                'Inhabilitado',
                'En mantenimiento',
                'Disponible'
            ]);
            $table->unsignedTinyInteger('nivelLlenado');
            $table->enum('tipo', [
                'Reciclables',
                'Mixtos'
            ]);
            $table->unsignedInteger('idRuta');

            $table->foreign('idRuta')
                ->references('idRuta')
                ->on('RUTA')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException('Esta migración requiere SQLite en memoria para Testing.');
        }

        Schema::dropIfExists('CONTENEDOR');
    }
};
