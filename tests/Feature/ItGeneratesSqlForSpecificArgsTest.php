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

        File::delete($expectedCustomPath);
    }
}
