<?php

namespace Tests\Feature;

use App\Http\Controllers\UsuarioController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuarioDeleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('usuario', function (Blueprint $table) {
            $table->increments('idUsu');
        });
        foreach (['integra_cuadrilla', 'asigna_ruta', 'reporta', 'controla', 'credenciales'] as $nombre) {
            Schema::create($nombre, function (Blueprint $table) use ($nombre) {
                $table->unsignedInteger('idUsu');
                $foreign = $table->foreign('idUsu')->references('idUsu')->on('usuario');
                $nombre === 'credenciales' ? $foreign->cascadeOnDelete() : $foreign->restrictOnDelete();
            });
        }
    }

    public function test_missing_user_returns_404(): void
    {
        $response = (new UsuarioController)->destroy('999');
        $this->assertSame(404, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
    }

    public function test_restricted_users_return_409_without_deleting_user_or_credentials(): void
    {
        foreach (['integra_cuadrilla', 'asigna_ruta', 'reporta', 'controla'] as $i => $tabla) {
            $id = $i + 1;
            DB::table('usuario')->insert(['idUsu' => $id]);
            DB::table('credenciales')->insert(['idUsu' => $id]);
            DB::table($tabla)->insert(['idUsu' => $id]);
            $response = (new UsuarioController)->destroy((string) $id);
            $this->assertSame(409, $response->getStatusCode());
            $this->assertFalse($response->getData(true)['success']);
            $this->assertStringContainsString('No se puede eliminar', $response->getData(true)['mensaje']);
            $this->assertTrue(DB::table('usuario')->where('idUsu', $id)->exists());
            $this->assertTrue(DB::table('credenciales')->where('idUsu', $id)->exists());
        }
    }

    public function test_unrestricted_user_is_deleted_with_credentials(): void
    {
        DB::table('usuario')->insert(['idUsu' => 1]);
        DB::table('credenciales')->insert(['idUsu' => 1]);
        $response = (new UsuarioController)->destroy('1');
        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);
        $this->assertFalse(DB::table('usuario')->where('idUsu', 1)->exists());
        $this->assertFalse(DB::table('credenciales')->where('idUsu', 1)->exists());
    }

    public function test_mysql_constraint_identifies_the_blocking_relationship(): void
    {
        DB::table('usuario')->insert(['idUsu' => 1]);
        DB::connection()->beforeExecuting(function ($query, $bindings, $connection) {
            if (str_starts_with(strtolower($query), 'delete')) {
                $pdo = new \PDOException('Cannot delete parent row: fk_integra_usuario');
                $pdo->errorInfo = ['23000', 1451, 'fk_integra_usuario'];
                throw new \Illuminate\Database\QueryException($connection->getName(), $query, $bindings, $pdo);
            }
        });
        $response = (new UsuarioController)->destroy('1');
        $this->assertSame(409, $response->getStatusCode());
        $this->assertStringContainsString('integra una cuadrilla', $response->getData(true)['mensaje']);
        $this->assertTrue(DB::table('usuario')->where('idUsu', 1)->exists());
    }

    public function test_unexpected_failure_returns_safe_json_without_database_details(): void
    {
        DB::connection()->beforeExecuting(function () {
            throw new \RuntimeException('private database details');
        });
        $response = (new UsuarioController)->destroy('1');
        $this->assertSame(500, $response->getStatusCode());
        $this->assertFalse($response->getData(true)['success']);
        $this->assertStringNotContainsString('private database details', $response->getContent());
        $this->assertStringContainsString('error inesperado', $response->getData(true)['mensaje']);
    }
}
