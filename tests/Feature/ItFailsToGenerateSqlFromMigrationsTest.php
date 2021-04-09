<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ItFailsToGenerateSqlFromMigrationsTest extends TestCase
{
    public function test_the_command_will_fail_to_output_to_file_when_there_are_no_migrations(): void
    {
        $this->withoutMigrations();

        $code   = Artisan::call('migrate:to-sql');
        $output = Artisan::output();

        $this->assertEquals(1, $code);

        $this->assertStringContainsString('No migrations to export!', $output);
    }

    public function test_the_command_will_fail_to_output_to_tty_when_there_are_no_migrations(): void
    {
        $this->withoutMigrations();

        $code   = Artisan::call('migrate:to-sql --tty');
        $output = Artisan::output();

        $this->assertEquals(1, $code);

        $this->assertStringContainsString('No migrations to export!', $output);
    }

    public function test_the_command_will_fail_to_output_to_a_custom_path_if_the_custom_path_is_invalid(): void
    {
        $this->withMigrations();
        $expectedCustomPath = '/un/writable/path';

        $code   = Artisan::call("migrate:to-sql --exportPath={$expectedCustomPath}");
        $output = Artisan::output();

        $this->assertEquals(1, $code);

        $this->assertStringContainsString(
            'Unable to write migration file.',
            $output
        );

        $this->assertStringContainsString(
            'Error Code:',
            $output
        );

        $this->assertStringContainsString(
            'Error Message:',
            $output
        );

        $this->assertFileDoesNotExist(
            $expectedCustomPath
        );
    }
}
