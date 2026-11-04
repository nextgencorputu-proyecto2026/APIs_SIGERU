
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite' ||
            DB::connection()->getDatabaseName() !== ':memory:') {
            throw new RuntimeException(
                'Esta migración solo puede ejecutarse en SQLite en memoria.'
            );
        }

        Schema::create('vehiculos', function (Blueprint $table) {
            $table->increments('idVehiculo');
            $table->string('matricula', 12)->unique();
            $table->string('marca', 40);
            $table->string('tipo', 50);
            $table->string('estado', 30);
            $table->decimal('capacidad', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite' ||
            DB::connection()->getDatabaseName() !== ':memory:') {
            throw new RuntimeException(
                'Esta migración solo puede revertirse en SQLite en memoria.'
            );
        }

        Schema::dropIfExists('vehiculos');
    }
};
