
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    public function up(): void
    {
        $this->verificarEntorno();

        Schema::create('centro', function (Blueprint $table) {
            $table->increments('idCentro');
        });

        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('idUsu');
            $table->string('ci', 20)->unique();
            $table->string('nombre1', 50);
            $table->string('nombre2', 50)->nullable();
            $table->string('apellido1', 50);
            $table->string('apellido2', 50)->nullable();
            $table->date('fec_nac');
            $table->string('tipo', 30);
            $table->unsignedInteger('idCentro');

            $table->foreign('idCentro')
                ->references('idCentro')
                ->on('centro');
        });

        Schema::create('credenciales', function (Blueprint $table) {
            $table->unsignedInteger('idUsu')->primary();
            $table->string('mail')->unique();
            $table->string('contrasena');

            $table->foreign('idUsu')
                ->references('idUsu')
                ->on('usuario')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        $this->verificarEntorno();

        Schema::dropIfExists('credenciales');
        Schema::dropIfExists('usuario');
        Schema::dropIfExists('centro');
    }

    private function verificarEntorno(): void
    {
        if (
            !app()->environment('testing') ||
            DB::getDefaultConnection() !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException(
                'Esta migración solo puede ejecutarse en testing con SQLite en memoria.'
            );
        }
    }
};
