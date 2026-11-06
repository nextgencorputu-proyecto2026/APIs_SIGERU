<?php
namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CentroCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
        Schema::create('centro', function (Blueprint $t) {
            $t->increments('idCentro'); $t->string('nombre'); $t->string('tipo'); $t->decimal('capacidad')->nullable();
        });
        Schema::create('usuario', function (Blueprint $t) {
            $t->increments('idUsu'); $t->unsignedInteger('idCentro');
            $t->foreign('idCentro')->references('idCentro')->on('centro')->restrictOnDelete();
        });
        DB::table('centro')->insert(['idCentro'=>1,'nombre'=>'Prueba','tipo'=>'Central Operativa','capacidad'=>null]);
    }

    public function test_null_capacity_can_be_set_when_changing_to_storage_center(): void
    {
        $this->putJson('/api/centros-acopio/1',['tipo'=>'Centro de acopio','capacidad'=>1500])->assertOk()->assertJsonPath('success',true);
        $this->assertEquals(1500,DB::table('centro')->where('idCentro',1)->value('capacidad'));
    }
    public function test_central_capacity_is_rejected_instead_of_silently_discarded(): void
    {
        $this->putJson('/api/centros-acopio/1',['capacidad'=>1500])->assertStatus(422)->assertJsonPath('success',false);
        $this->assertNull(DB::table('centro')->where('idCentro',1)->value('capacidad'));
    }
    public function test_explicit_null_capacity_is_rejected_for_storage_center(): void
    {
        DB::table('centro')->where('idCentro',1)->update(['tipo'=>'Centro de acopio','capacidad'=>500]);
        $this->putJson('/api/centros-acopio/1',['capacidad'=>null])->assertStatus(422);
        $this->assertEquals(500,DB::table('centro')->where('idCentro',1)->value('capacidad'));
    }
    public function test_omitted_capacity_keeps_existing_value(): void
    {
        DB::table('centro')->where('idCentro',1)->update(['tipo'=>'Centro de acopio','capacidad'=>500]);
        $this->putJson('/api/centros-acopio/1',['nombre'=>'Nuevo nombre'])->assertOk();
        $this->assertEquals(500,DB::table('centro')->where('idCentro',1)->value('capacidad'));
    }
    public function test_changing_to_central_clears_capacity(): void
    {
        DB::table('centro')->where('idCentro',1)->update(['tipo'=>'Centro de acopio','capacidad'=>500]);
        $this->putJson('/api/centros-acopio/1',['tipo'=>'Central Operativa','capacidad'=>null])->assertOk();
        $this->assertNull(DB::table('centro')->where('idCentro',1)->value('capacidad'));
    }
    public function test_restricted_delete_returns_conflict_and_preserves_center(): void
    {
        DB::table('usuario')->insert(['idUsu'=>1,'idCentro'=>1]);
        $this->deleteJson('/api/centros-acopio/1')->assertStatus(409)->assertJsonPath('success',false);
        $this->assertTrue(DB::table('centro')->where('idCentro',1)->exists());
    }
    public function test_free_center_is_deleted_and_missing_center_returns_404(): void
    {
        $this->deleteJson('/api/centros-acopio/1')->assertOk()->assertJsonPath('success',true);
        $this->assertFalse(DB::table('centro')->where('idCentro',1)->exists());
        $this->deleteJson('/api/centros-acopio/1')->assertNotFound();
    }
}
