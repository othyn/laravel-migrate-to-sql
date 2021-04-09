<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ItGeneratesSqlFromMigrationsTest extends TestCase
{
    public function test_the_command_can_provide_help(): void
    {
        $this->withoutMigrations();

        Artisan::call('migrate:to-sql --help');

        $this->assertStringContainsString('Generates SQL from your applications migrations', Artisan::output());
    }

    public function test_by_default_the_command_exports_up_migrations_to_file(): void
    {
        $this->withMigrations();
        $expectedType = 'up';

        $code   = Artisan::call('migrate:to-sql');
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
}
