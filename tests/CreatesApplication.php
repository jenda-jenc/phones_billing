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
        $connection = env('DB_REFRESH_CONNECTION', env('DB_CONNECTION'));

        if ($connection === null || $connection === '') {
            return;
        }

        $this->ensureSqliteDatabaseExists($connection);

        if (self::$databaseMigrated) {
            return;
        }

        Artisan::call('migrate:fresh', [
            '--database' => $connection,
            '--force' => true,
        ]);

        self::$databaseMigrated = true;
    }

    private function ensureSqliteDatabaseExists(string $connection): void
    {
        $config = config("database.connections.$connection");

        if (! is_array($config) || ($config['driver'] ?? null) !== 'sqlite') {
            return;
        }

        $database = $config['database'] ?? null;

        if ($database === null || $database === '' || $database === ':memory:') {
            return;
        }

        if (! str_starts_with($database, DIRECTORY_SEPARATOR) && ! preg_match('/^[A-Za-z]:\\\\/', $database)) {
            $database = database_path($database);
            config(["database.connections.$connection.database" => $database]);
        }

        $directory = dirname($database);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        if (! file_exists($database)) {
            touch($database);
        }
    }
}
