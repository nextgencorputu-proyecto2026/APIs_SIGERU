<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:' ||
            $this->app['db']->connection()->getDriverName() !== 'sqlite' ||
            $this->app['db']->connection()->getDatabaseName() !== ':memory:'
        ) {
            throw new RuntimeException(
                'Los tests de SiGeRU deben utilizar SQLite en memoria.'
            );
        }
    }

    protected function migrateFreshUsing()
    {
        return [
            '--database' => 'sqlite',
            '--path' => [
                'database/migrations',
                'database/migrations/testing',
            ],
            '--realpath' => false,
        ];
    }
}
