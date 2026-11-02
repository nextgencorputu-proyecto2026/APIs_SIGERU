<?php

use Tests\Concerns\RefreshSigeruDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshSigeruDatabase::class);

test('la base de testing utiliza SQLite en memoria', function () {
    expect(config('database.default'))->toBe('sqlite');
    expect(config('database.connections.sqlite.database'))->toBe(':memory:');
});

test('las tablas principales de SiGeRU existen en testing', function () {
    expect(Schema::hasTable('RUTA'))->toBeTrue();
    expect(Schema::hasTable('CONTENEDOR'))->toBeTrue();
    expect(Schema::hasTable('usuario'))->toBeTrue();
    expect(Schema::hasTable('credenciales'))->toBeTrue();
    expect(Schema::hasTable('incidencia'))->toBeTrue();
    expect(Schema::hasTable('vehiculos'))->toBeTrue();
    expect(Schema::hasTable('cuadrilla'))->toBeTrue();
});

test('las tablas de relaciones existen en testing', function () {
    expect(Schema::hasTable('contenedor_presenta'))->toBeTrue();
    expect(Schema::hasTable('vehiculo_presenta'))->toBeTrue();
    expect(Schema::hasTable('reporta'))->toBeTrue();
    expect(Schema::hasTable('controla'))->toBeTrue();
    expect(Schema::hasTable('atiende'))->toBeTrue();
    expect(Schema::hasTable('asigna_ruta'))->toBeTrue();
    expect(Schema::hasTable('cumple_barredora'))->toBeTrue();
    expect(Schema::hasTable('cumple_recoleccion'))->toBeTrue();
    expect(Schema::hasTable('se_realiza_en'))->toBeTrue();
});
