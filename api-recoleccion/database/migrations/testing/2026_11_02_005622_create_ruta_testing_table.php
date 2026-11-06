<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

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

        Schema::create('RUTA', function (Blueprint $table) {
            $table->increments('idRuta');
            $table->string('descripcion', 255);
            $table->time('horario');
            $table->enum('tipoResiduo', [
                'Mixtos',
                'Reciclables'
            ]);
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

        Schema::dropIfExists('RUTA');
    }
};
