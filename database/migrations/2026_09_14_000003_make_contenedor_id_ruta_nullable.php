<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contenedor', function (Blueprint $table) {
            $table->dropForeign('fk_contenedor_ruta');
        });

        Schema::table('contenedor', function (Blueprint $table) {
            $table->unsignedInteger('idRuta')->nullable()->change();
            $table->foreign('idRuta', 'fk_contenedor_ruta')
                ->references('idRuta')
                ->on('ruta')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contenedor', function (Blueprint $table) {
            $table->dropForeign('fk_contenedor_ruta');
        });

        Schema::table('contenedor', function (Blueprint $table) {
            $table->unsignedInteger('idRuta')->nullable(false)->change();
            $table->foreign('idRuta', 'fk_contenedor_ruta')
                ->references('idRuta')
                ->on('ruta')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }
};
