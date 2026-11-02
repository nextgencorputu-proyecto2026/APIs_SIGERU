<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!app()->environment('testing')) {
            throw new RuntimeException('Esta migración es exclusiva de Testing.');
        }

        Schema::create('RUTA', function (Blueprint $table) {
            $table->increments('idRuta');
            $table->string('descripcion', 255);
            $table->time('horario');
            $table->enum('tipoResiduo', ['Mixtos', 'Reciclables']);
        });
    }

    public function down(): void
    {
        if (!app()->environment('testing')) {
            throw new RuntimeException('Esta migración es exclusiva de Testing.');
        }

        Schema::dropIfExists('RUTA');
    }
};
