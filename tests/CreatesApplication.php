<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $this->ensureEnvironmentFileExists();

        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->ensureSqliteDatabaseExists();

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

    private function ensureSqliteDatabaseExists(): void
    {
        if (env('DB_CONNECTION') !== 'sqlite') {
            return;
        }

        $databasePath = database_path('database.sqlite');

        if (! file_exists($databasePath)) {
            touch($databasePath);
        }
    }
}
