<?php

namespace Othyn\MigrateToSql\Models;

use Illuminate\Database\Connection;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Arr;

/**
 * An export representation of the requirements of a Migration.
 */
class Migration
{
    /**
     * The migrator instance.
     */
    protected Migrator $migrator;

    /**
     * The database connection to use when polling the database in pretend mode to generate the SQL queries.
     */
    public Connection $databaseConnection;

    /**
     * The migration file path on disk.
     */
    public string $migrationFile;

    /**
     * The migration filename on disk.
     */
    public string $migrationFilename;

    /**
     * The loaded instance of the migration file in memory.
     */
    public object $migrationInstance;

    /**
     * The filename of the migration in an SQL comment style.
     */
    public string $nameComment;

    /**
     * The determined SQL queries for the migration after being run against the database in pretend mode.
     */
    public array $queries;

    /**
     * Setup!
     */
    public function __construct(string $migrationFile)
    {
        $this->migrator = app('migrator');

        // Using a null connection is easier than an sqlite in :memory:
        $this->databaseConnection = $this->migrator->resolveConnection(null);

        $this->migrationFile = $migrationFile;

        $this->migrationFilename = $this->migrator->getMigrationName($migrationFile);

        $this->migrationInstance = $this->migrator->resolve($this->migrationFilename);

        // SQL file comment style of the filename for reference in output
        $this->nameComment = "-- {$this->migrationFilename}:";
    }

    /**
     * Gets all the queries as SQL by running them against the database in pretend mode.
     */
    public function generateQueries(string $type): void
    {
        $queryLogs = $this->databaseConnection
            ->pretend(function () use ($type) {
                if (method_exists($this->migrationInstance, $type)) {
                    $this->migrationInstance->{$type}();
                }
            });

        $this->queries = Arr::pluck($queryLogs, 'query');
    }
}
