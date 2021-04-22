<?php

namespace Othyn\MigrateToSql\Libraries;

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Collection;
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
     * Stores the raw connection name for checking if the user optioned it as the connection instance will load the
     * default when null is passed.
     */
    protected ?string $connectionName;

    /**
     * The database connection to use when polling the database in pretend mode to generate the SQL queries.
     */
    protected Connection $databaseConnection;

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
    public function __construct(array $migrationFilePaths, ?string $connection)
    {
        $this->migrator = app('migrator');

        $this->connectionName = $connection;

        $this->databaseConnection = $this->migrator->resolveConnection($connection);

        $this->loadMigrationFiles($migrationFilePaths);
    }

    /**
     * Loads the migration files from disk and requires them into the application so they can be called.
     */
    protected function loadMigrationFiles(array $migrationFilePaths): void
    {
        $runMigrations = [];

        // Only check for existing values if the user has specified a connection indicating that they want to generate
        // a partial patch file based on the migration state of the connection database.
        if (!is_null($this->connectionName)) {
            $runMigrations = $this->migrator->getRepository()->getRan();
        }

        $migrationFiles = $this->migrator->getMigrationFiles($migrationFilePaths);

        $this->migrationFiles = Collection::make($migrationFiles)
                ->reject(function ($file) use ($runMigrations) {
                    return in_array($this->migrator->getMigrationName($file), $runMigrations);
                })
                ->values()
                ->all();

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
            $migration = new Migration($this->migrator, $migrationFile);

            $migration->generateQueries($this->databaseConnection, $type);

            $this->output->processMigration($migration);
        }

        return $this->output->prepareDump();
    }
}
