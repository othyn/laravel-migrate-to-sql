<?php

namespace Othyn\MigrateToSql\Console;

use Illuminate\Database\Console\Migrations\BaseCommand;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Carbon;
use Othyn\MigrateToSql\Libraries\MigrationExporter;
use Othyn\MigrateToSql\MigrationOutputs\FileMigrationOutput;
use Othyn\MigrateToSql\MigrationOutputs\TtyMigrationOutput;

/**
 * Helper Artisan command that will export all migrations to an SQL file.
 */
class MigrateToSql extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:to-sql
                            {--type=up : Which type of migration to generate the SQL for; up or down}
                            {--exportPath= : The output path for the generated SQL file, defaults to base_path() of the application}
                            {--ugly : Queries should not be prettified as part of the output process}
                            {--tty : Output should be sent to TTY instead of written to disk, use `--no-ansi` to disable output formatting}
                            {--connection= : The database connection in which to generate migrations against. The default will generate all migrations, or connect it to an active database connection to only generate for migrations that have not already been run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates SQL from your applications migrations';

    /**
     * The migrator instance.
     */
    protected Migrator $migrator;

    /**
     * Helper for the date & time that the command was run.
     */
    protected Carbon $currentDate;

    /**
     * Which type of migration to generate the SQL for; up or down.
     */
    protected string $type;

    /**
     * The output path for the generated SQL file, defaults to base_path() of the application.
     */
    protected string $exportPath;

    /**
     * Queries should not be prettified as part of the output process.
     */
    protected bool $ugly;

    /**
     * Output should be sent to TTY instead of written to disk.
     */
    protected bool $tty;

    /**
     * Database connection to use when generating migrations.
     */
    protected ?string $connection;

    /**
     * TTY output should be prettified.
     */
    protected bool $ttyPretty;

    /**
     * Create a new migration command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->migrator = app('migrator');

        $this->currentDate = now();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // This cannot be done in the constructor, as Symfony hasn't done its thing yet
        $this->parseArgs();

        $migrationExporter = new MigrationExporter($this->getMigrationPaths(), $this->connection);

        if ($this->tty) {
            $migrationOutput = new TtyMigrationOutput($this->output, $this->ugly);

            $migrationOutput->setTtyPretty($this->ttyPretty);
        } else {
            $migrationOutput = new FileMigrationOutput($this->output, $this->ugly);

            $migrationOutput->setPath($this->exportPath);
        }

        $migrationExporter->setMigrationOutput($migrationOutput);

        return (int) !$migrationExporter->export($this->type);
    }

    /**
     * In the spirit of keeping things tidy, just collect any and all arguments, options, etc. values in here.
     */
    protected function parseArgs(): void
    {
        // Just a wee bit of validation to ensure only known values are passed
        $this->type = $this->option('type') === 'down' ? 'down' : 'up';

        // Build the default path if not provided
        $this->exportPath = $this->option('exportPath') ?? base_path("migrations.{$this->type}.{$this->currentDateFormatted()}.sql");

        $this->ugly       = $this->option('ugly');
        $this->tty        = $this->option('tty');
        $this->connection = $this->option('connection');

        $this->ttyPretty = $this->output->getFormatter()->isDecorated();
    }

    /**
     * Helper to return the currentDate in a pre-formatted string.
     */
    protected function currentDateFormatted(): string
    {
        return $this->currentDate->format('Y_m_d__His');
    }
}
