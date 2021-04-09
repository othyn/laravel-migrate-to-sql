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
                [
                    'type' => '',
                    'ugly' => false,
                ],
            ],
            'type up' => [
                'up',
                [
                    'type' => 'up',
                    'ugly' => false,
                ],
            ],
            'type down' => [
                'down',
                [
                    'type' => 'down',
                    'ugly' => false,
                ],
            ],
            'type up but damn its ugly' => [
                'up',
                [
                    'type' => 'up',
                    'ugly' => true,
                ],
            ],
            'type down but damn its ugly' => [
                'down',
                [
                    'type' => 'down',
                    'ugly' => true,
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideTypes
     */
    public function test_the_command_exports_migrations_to_file(string $expectedType, array $actualTypeAndUgly): void
    {
        $this->withMigrations();
        $actualType = $actualTypeAndUgly['type'];
        $actualUgly = $actualTypeAndUgly['ugly'];

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
    public function test_the_command_exports_migrations_to_tty(string $expectedType, array $actualTypeAndUgly): void
    {
        $this->withMigrations();
        $actualType = $actualTypeAndUgly['type'];
        $actualUgly = $actualTypeAndUgly['ugly'];

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
    public function test_the_command_exports_migrations_to_a_custom_path(string $expectedType, array $actualTypeAndUgly): void
    {
        $this->withMigrations();
        $actualType         = $actualTypeAndUgly['type'];
        $actualUgly         = $actualTypeAndUgly['ugly'];
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

        File::delete($expectedCustomPath);
    }
}
