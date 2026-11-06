<?php

namespace Tests\Concerns;

use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

trait RefreshSigeruDatabase
{
    use RefreshDatabase {
        refreshDatabase as protected laravelRefreshDatabase;
    }

    protected function refreshDatabase(): void
    {
        if (
            !app()->environment('testing') ||
            config('database.default') !== 'sqlite' ||
            config('database.connections.sqlite.database') !== ':memory:'
        ) {
            throw new RuntimeException(
                'Las migraciones de testing solo pueden ejecutarse con SQLite en memoria.'
            );
        }

        $this->laravelRefreshDatabase();
    }

    protected function migrateFreshUsing(): array
    {
        return [
            '--path' => 'database/migrations/testing',
            '--realpath' => false,
        ];
    }
}
