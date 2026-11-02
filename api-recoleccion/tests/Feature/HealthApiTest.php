<?php

test('el endpoint de health responde correctamente', function () {
    $this->getJson('/api/health')
        ->assertSuccessful()
        ->assertJsonPath('success', true);
});
test('testing utiliza SQLite en memoria', function () {
    expect(config('database.default'))->toBe('sqlite');

    expect(config('database.connections.sqlite.database'))
        ->toBe(':memory:');
});