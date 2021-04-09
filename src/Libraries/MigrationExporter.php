<?php

namespace Othyn\MigrateToSql\Libraries;

use Illuminate\Database\Migrations\Migrator;
use Othyn\MigrateToSql\Interfaces\MigrationOutputInterface;
use Othyn\MigrateToSql\Models\Migration;

/**
 * Practically orchestrates the loading of the migrations from disk and instructing the Migration and Migration Outputters
 * what to do with the data.
 */
class MigrationExporter
{
    /**
     * The migrator instance.
     */
    protected Migrator $migrator;

    /**
     * The loaded migration files from disk.
     */
    protected array $migrationFiles;

    /**
     * The Migration Outputter to use when it comes time to export the migration queries.
     */
    protected MigrationOutputInterface $output;

    /**
     * Setup time!
     */
    public function __construct(array $migrationFilePaths)
    {
        $this->migrator = app('migrator');

        $this->loadMigrationFiles($migrationFilePaths);
    }

    /**
     * Loads the migration files from disk and requires them into the application so they can be called.
     */
    protected function loadMigrationFiles(array $migrationFilePaths): void
    {
        $this->migrationFiles = $this->migrator->getMigrationFiles($migrationFilePaths);

        $this->migrator->requireFiles($this->migrationFiles);
    }

    /**
     * Sets the output that the exporter should use.
     */
    public function setMigrationOutput(MigrationOutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * Runs the migration SQL export via the defined output instance.
     */
    public function export(string $type): bool
    {
        foreach ($this->migrationFiles as $migrationFile) {
            $migration = new Migration($migrationFile);

            $migration->generateQueries($type);

            $this->output->processMigration($migration);
        }

        return $this->output->prepareDump();
    }
}
