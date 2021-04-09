<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Othyn\MigrateToSql\Providers\MigrateToSqlServiceProvider;
use Spatie\TestTime\TestTime;

abstract class TestCase extends BaseTestCase
{
    /**
     * Path for the test data is fixed, so const-sistency is key.
     */
    const TEST_DATA_PATH = __DIR__.'/../test_data';

    /**
     * Path for the test migrations is fixed, so const-sistency is key.
     */
    const TEST_MIGRATIONS_PATH = self::TEST_DATA_PATH.'/migrations';

    /**
     * Path for the test output is fixed, so const-sistency is key.
     */
    const TEST_OUTPUT_PATH = self::TEST_DATA_PATH.'/expected_output';

    /**
     * The orchestra package directory is dynamic, so props to it I guess...
     */
    protected string $laravelMigrationPath;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // This needs to be triggered so that the filename that the export generates is predictable
        TestTime::freeze();

        $this->laravelMigrationPath = base_path('database/migrations');
    }

    /**
     * Clean up the test environment.
     */
    protected function tearDown(): void
    {
        // Remove any testing output fragments that may have been generated as to not pollute other tests
        if (File::exists($this->migrationSqlExportPath('up'))) {
            File::delete($this->migrationSqlExportPath('up'));
        }
        if (File::exists($this->migrationSqlExportPath('down'))) {
            File::delete($this->migrationSqlExportPath('down'));
        }
    }

    /**
     * Any service providers that the test may require.
     */
    protected function getPackageProviders($app): array
    {
        return [
            MigrateToSqlServiceProvider::class,
        ];
    }

    /**
     * Copy in the testing migrations, so tests have something to migrate.
     */
    protected function withMigrations(): void
    {
        if (!File::isDirectory($this->laravelMigrationPath)) {
            File::makeDirectory($this->laravelMigrationPath);
        }

        File::copyDirectory(self::TEST_MIGRATIONS_PATH, $this->laravelMigrationPath);
    }

    /**
     * Delete any currently copied testing migrations, so tests have nothing to migrate.
     */
    protected function withoutMigrations(): void
    {
        if (File::isDirectory($this->laravelMigrationPath)) {
            File::deleteDirectory($this->laravelMigrationPath);
        }
    }

    /**
     * Gets the path of the expected output file so it can be compared.
     */
    protected function expectedTestDataPath(string $type, bool $ugly = false): string
    {
        $uglySlug = $ugly ? '.ugly' : '';

        return self::TEST_OUTPUT_PATH."/migrations.{$type}{$uglySlug}.sql";
    }

    /**
     * Builds the expected format for the migration export file.
     */
    protected function migrationSqlExportPath(string $type): string
    {
        $date              = now()->format('Y_m_d__His');
        $migrationFileName = "migrations.{$type}.{$date}.sql";

        return base_path($migrationFileName);
    }
}
