<?php

namespace Othyn\MigrateToSql\Interfaces;

use Othyn\MigrateToSql\Models\Migration;

/**
 * All Migration Outputters must adhere to this interface as a minimum.
 */
interface MigrationOutputInterface
{
    /**
     * Formats the SQL query as to which preferences the user has provided.
     */
    public function formatQuery(string $query): string;

    /**
     * Processes the migration's query with the child classes processor.
     */
    public function processMigration(Migration $migration): void;

    /**
     * Dumps standardised output to the tty session along with any custom data wishing to be dumped in the child class.
     */
    public function prepareDump(): bool;
}
