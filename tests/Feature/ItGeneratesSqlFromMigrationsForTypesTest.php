<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ItGeneratesSqlFromMigrationsForTypesTest extends TestCase
{
    public function provideTypes(): array
    {
        return [
            'type up by default' => [
                'up',
                '',
                false,
            ],
            'type up' => [
                'up',
                'up',
                false,
            ],
            'type down' => [
                'down',
                'down',
                false,
            ],
            'type up but damn its ugly' => [
                'up',
                'up',
                true,
            ],
            'type down but damn its ugly' => [
                'down',
                'down',
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideTypes
     */
    public function test_the_command_exports_migrations_to_file(string $expectedType, string $actualType, bool $actualUgly): void
    {
        $this->withMigrations();

        $command = "migrate:to-sql --type={$actualType}".($actualUgly ? ' --ugly' : '');

        $code   = Artisan::call($command);
        $output = Artisan::output();

        $this->assertEquals(0, $code);

        $this->assertStringContainsString(
            $this->migrationSqlExportPath($expectedType),
            $output
        );

        $this->assertFileExists(
            $this->migrationSqlExportPath($expectedType)
        );

        $this->assertFileEquals(
            $this->expectedTestDataPath($expectedType, $actualUgly),
            $this->migrationSqlExportPath($expectedType)
        );
    }

    /**
     * @dataProvider provideTypes
     */
    public function test_the_command_exports_migrations_to_tty(string $expectedType, string $actualType, bool $actualUgly): void
    {
        $this->withMigrations();

        $command = "migrate:to-sql --type={$actualType} --tty".($actualUgly ? ' --ugly' : '');

        $code   = Artisan::call($command);
        $output = Artisan::output();

        $this->assertEquals(0, $code);

        $this->assertFalse(
            File::exists($this->migrationSqlExportPath($expectedType))
        );

        $this->assertStringEqualsFile(
            $this->expectedTestDataPath($expectedType, $actualUgly),
            trim($output)
        );
    }

    /**
     * @dataProvider provideTypes
     */
    public function test_the_command_exports_migrations_to_a_custom_path(string $expectedType, string $actualType, bool $actualUgly): void
    {
        $this->withMigrations();

        $expectedCustomPath = base_path(time().'.custom.sql');

        $command = "migrate:to-sql --type={$actualType} --exportPath={$expectedCustomPath}".($actualUgly ? ' --ugly' : '');

        $code   = Artisan::call($command);
        $output = Artisan::output();

        $this->assertEquals(0, $code);

        $this->assertStringContainsString(
            $expectedCustomPath,
            $output
        );

        $this->assertFileExists(
            $expectedCustomPath
        );

        $this->assertFileEquals(
            $this->expectedTestDataPath($expectedType, $actualUgly),
            $expectedCustomPath
        );
    }

    /**
     * @dataProvider provideTypes
     */
    public function test_the_command_exports_migrations_for_a_custom_connection(string $expectedType, string $actualType, bool $actualUgly): void
    {
        // This refers to Orchestra's testing connection that sets the driver to sqlite and database to :memory:
        $connection = 'testing';

        // Setup the base migrations, placing them in the required location for the migration to occur
        $this->withMigrations();

        // Take a backup of the connection prior to changing it for this test so it can be reset
        $originalDefaultConnection = config('database.default');

        // Set the DB connection to sqlite and in :memory: as a test DB to migrate into a partial state with pending
        // 'development' migrations that require patching to 'live'
        config(['database.default' => $connection]);

        // Run the migrations via artisan (or helper) to migrate the in memory DB with the test migrations
        Artisan::call('migrate:fresh');

        // Ensure the migration state as expected
        $this->assertDatabaseHas('migrations', [
            'migration' => '2014_10_12_000000_create_users_table',
        ], $connection);

        $this->assertDatabaseHas('migrations', [
            'migration' => '2014_10_12_100000_create_password_resets_table',
        ], $connection);

        $this->assertDatabaseMissing('migrations', [
            'migration' => '2019_08_19_000000_create_failed_jobs_table',
        ], $connection);

        // Copy across the partial_migrations to the testing migration directory
        File::copyDirectory(self::TEST_PARTIAL_MIGRATIONS_PATH, $this->laravelMigrationPath);

        // Run the CLI tool as with the other tests
        $command = "migrate:to-sql --type={$actualType} --connection={$connection}".($actualUgly ? ' --ugly' : '');

        $code   = Artisan::call($command);
        $output = Artisan::output();

        // Verify that only the partial_migrations appear in the output fragment
        $this->assertEquals(0, $code);

        $this->assertStringContainsString(
            $this->migrationSqlExportPath($expectedType),
            $output
        );

        $this->assertFileExists(
            $this->migrationSqlExportPath($expectedType)
        );

        $this->assertFileEquals(
            $this->expectedTestDataPath($expectedType, $actualUgly, true),
            $this->migrationSqlExportPath($expectedType)
        );

        // Reset DB connection
        config(['database.default' => $originalDefaultConnection]);
    }
}
