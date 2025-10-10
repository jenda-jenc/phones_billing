<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;

trait CreatesApplication
{
    private static bool $databaseMigrated = false;

    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $this->ensureEnvironmentFileExists();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->refreshDatabaseSchema();

        return $app;
    }

    private function ensureEnvironmentFileExists(): void
    {
        $basePath = dirname(__DIR__);
        $environmentFile = $basePath.'/.env';

        if (file_exists($environmentFile)) {
            return;
        }

        $exampleFile = $basePath.'/.env.example';

        if (file_exists($exampleFile)) {
            copy($exampleFile, $environmentFile);
        } else {
            touch($environmentFile);
        }
    }

    private function refreshDatabaseSchema(): void
    {
        if (self::$databaseMigrated) {
            return;
        }

        $connection = env('DB_REFRESH_CONNECTION', env('DB_CONNECTION'));

        if ($connection === null || $connection === '') {
            return;
        }

        Artisan::call('migrate:fresh', [
            '--database' => $connection,
            '--force' => true,
        ]);

        self::$databaseMigrated = true;
    }
}
