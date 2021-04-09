<?php

namespace Othyn\MigrateToSql\MigrationOutputs;

use Doctrine\SqlFormatter\NullHighlighter;
use Doctrine\SqlFormatter\SqlFormatter;
use Illuminate\Console\OutputStyle;
use Othyn\MigrateToSql\Interfaces\MigrationOutputInterface;
use Othyn\MigrateToSql\Models\Migration;

/**
 * Base functionality of all required Migration Outputters.
 */
abstract class BaseMigrationOutput implements MigrationOutputInterface
{
    /**
     * Stores the accumulative total of all of the migrated queries.
     */
    protected int $migratedQueryCount = 0;

    /**
     * The collection of output lines returned from each call of processMigrationQuery().
     */
    protected array $collectedOutputLines = [];

    /**
     * The console output interface to use when dumping to a console tty session.
     */
    protected OutputStyle $tty;

    /**
     * Whether the output SQL should be ugly.
     */
    protected bool $ugly;

    /**
     * The instance of the SQL formatter to use when not ugly.
     */
    protected SqlFormatter $sqlFormatter;

    /**
     * Setup the necessary class data.
     */
    public function __construct(OutputStyle $tty, bool $ugly)
    {
        $this->tty = $tty;

        $this->ugly = $ugly;

        $this->sqlFormatter = new SqlFormatter(new NullHighlighter());
    }

    /**
     * Formats the SQL query as to which preferences the user has provided.
     */
    public function formatQuery(string $query): string
    {
        $baseQuery = $this->ugly
            ? $query
            : $this->sqlFormatter->format($query, '    ', true);

        return "{$baseQuery};";
    }

    /**
     * Processes the migration's query with the child classes processor.
     */
    public function processMigration(Migration $migration): void
    {
        $this->migratedQueryCount += count($migration->queries);

        foreach ($migration->queries as $query) {
            $this->collectedOutputLines[] = $this->processMigrationQuery($migration, $query);
        }
    }

    /**
     * Dumps standardised output to the tty session along with any custom data wishing to be dumped in the child class.
     */
    public function prepareDump(): bool
    {
        if ($this->migratedQueryCount == 0) {
            $this->tty->info('No migrations to export!');

            // Should it really exit with a code > 0? As its technically not an error? Hmm...
            return false;
        }

        return $this->dump();
    }

    /**
     * Called on each query that a migration has, so the query can be formatted into the desired output.
     */
    abstract protected function processMigrationQuery(Migration $migration, string $query): ?string;

    /**
     * Called to do the actual heavy lifting of the output, if the child class requires it.
     * Some may choose to output in the query loop instead.
     */
    abstract protected function dump(): bool;
}
