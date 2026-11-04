<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;

trait RefreshSigeruDatabase
{
    protected function refreshSigeruDatabase(): void
    {
        if (
            DB::getDriverName() !== 'sqlite' ||
            DB::connection()->getDatabaseName() !== ':memory:'
        ) {
            throw new RuntimeException(
                'Los tests solo pueden utilizar SQLite en memoria.'
            );
        }

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/testing',
            '--realpath' => false,
            '--force' => true,
        ]);
    }
}
