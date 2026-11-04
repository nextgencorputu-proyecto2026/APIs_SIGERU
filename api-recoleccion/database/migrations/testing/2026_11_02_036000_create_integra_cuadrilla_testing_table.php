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
                'Esta migración es solo para SQLite de testing.'
            );
        }

        Schema::create('integra_cuadrilla', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('idCuadrilla');
            $table->unsignedInteger('idUsu');
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
                'Esta migración es solo para SQLite de testing.'
            );
        }

        Schema::dropIfExists('integra_cuadrilla');
    }
};
