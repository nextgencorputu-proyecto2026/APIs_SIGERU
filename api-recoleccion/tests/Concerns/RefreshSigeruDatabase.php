<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

trait RefreshSigeruDatabase
{
    use RefreshDatabase;

    protected function migrateFreshUsing()
    {
        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:' ||
            $this->app['db']->connection()->getDriverName() !== 'sqlite' ||
            $this->app['db']->connection()->getDatabaseName() !== ':memory:'
        ) {
            throw new RuntimeException(
                'Las migraciones de Testing requieren SQLite en memoria.'
            );
        }

        return [
            '--database' => 'sqlite',
            '--path' => [
                database_path('migrations'),
                database_path('migrations/testing'),
            ],
            '--realpath' => true,
        ];
    }
}
