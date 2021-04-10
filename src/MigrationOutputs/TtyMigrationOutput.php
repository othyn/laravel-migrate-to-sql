<?php

namespace Othyn\MigrateToSql\MigrationOutputs;

use Othyn\MigrateToSql\Models\Migration;

/**
 * Handles exporting Migration Queries to TTY.
 */
class TtyMigrationOutput extends BaseMigrationOutput
{
    /**
     * Whether the output should be prettified, --no-ansi dependant.
     */
    protected bool $ttyPretty;

    /**
     * Sets ttyPretty.
     */
    public function setTtyPretty(bool $ttyPretty): void
    {
        $this->ttyPretty = $ttyPretty;
    }

    /**
     * Output the queries to tty as they come through, use of writeln stops lines being written with an indenting space,
     * such as with text() or info().
     */
    protected function processMigrationQuery(Migration $migration, string $query): ?string
    {
        $nameOutputMethod = $this->ttyPretty ? 'info' : 'writeln';

        $this->tty->{$nameOutputMethod}($migration->nameComment);

        $this->tty->writeln(
            $this->formatQuery($query)
        );

        $this->tty->newLine();

        return null;
    }

    /**
     * Do nothing, as the primary output has been completed in the migration query processing loop.
     */
    protected function dump(): bool
    {
        return true;
    }
}
