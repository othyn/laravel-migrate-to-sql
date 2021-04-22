<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ItGeneratesSqlForSpecificArgsTest extends TestCase
{
    public function test_when_no_other_args_are_passed_the_command_exports_down_migrations_to_file(): void
    {
        $this->withMigrations();
        $expectedType = 'down';

        $code   = Artisan::call("migrate:to-sql --type={$expectedType}");
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
            $this->expectedTestDataPath($expectedType),
            $this->migrationSqlExportPath($expectedType)
        );
    }

    public function test_when_no_other_args_are_passed_the_command_exports_uglified_migrations_to_file_with_type_up_by_default(): void
    {
        $this->withMigrations();
        $expectedType = 'up';
        $expectedUgly = true;

        $code   = Artisan::call('migrate:to-sql --ugly');
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
            $this->expectedTestDataPath($expectedType, $expectedUgly),
            $this->migrationSqlExportPath($expectedType)
        );
    }

    public function test_when_no_other_args_are_passed_the_command_exports_migrations_to_tty_with_type_up_by_default(): void
    {
        $this->withMigrations();
        $expectedType = 'up';

        $code   = Artisan::call('migrate:to-sql --tty');
        $output = Artisan::output();

        $this->assertEquals(0, $code);

        $this->assertFalse(
            File::exists($this->migrationSqlExportPath($expectedType))
        );

        $this->assertStringEqualsFile(
            $this->expectedTestDataPath($expectedType),
            trim($output)
        );
    }

    public function test_when_no_other_args_are_passed_the_command_will_output_to_a_custom_path_with_type_up_by_default(): void
    {
        $this->withMigrations();

        $expectedType       = 'up';
        $expectedCustomPath = base_path(time().'.custom.sql');

        $code   = Artisan::call("migrate:to-sql --exportPath={$expectedCustomPath}");
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
            $this->expectedTestDataPath($expectedType),
            $expectedCustomPath
        );
    }

    public function test_when_no_other_args_are_passed_the_command_will_generate_migrations_for_a_custom_connection_with_type_up_by_default(): void
    {
        $expectedType = 'up';

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
        $code   = Artisan::call("migrate:to-sql --connection={$connection}");
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
            $this->expectedTestDataPath($expectedType, false, true),
            $this->migrationSqlExportPath($expectedType)
        );

        // Reset DB connection
        config(['database.default' => $originalDefaultConnection]);
    }
}
