<?php

namespace Othyn\MigrateToSql\MigrationOutputs;

use Othyn\MigrateToSql\Models\Migration;
use Throwable;

/**
 * Handles exporting Migration Queries to disk.
 */
class FileMigrationOutput extends BaseMigrationOutput
{
    /**
     * Stores the defined variable file export path.
     */
    protected string $exportPath;

    /**
     * Sets the variable export path, can be system or user defined.
     */
    public function setPath(string $exportPath): void
    {
        $this->exportPath = $exportPath;
    }

    /**
     * Build the lines from the migration and query data that will end up being dumped to disk.
     */
    protected function processMigrationQuery(Migration $migration, string $query): ?string
    {
        $fileContents = $migration->nameComment;
        $fileContents .= "\n";
        $fileContents .= $this->formatQuery($query);

        return $fileContents;
    }

    /**
     * Export the collected output lines to disk.
     */
    protected function dump(): bool
    {
        $fileContents = implode("\n\n", $this->collectedOutputLines);

        try {
            $writeSuccess = file_put_contents($this->exportPath, trim($fileContents)) > 0;
        } catch (Throwable $e) {
            $writeSuccess      = false;
            $writeErrorCode    = $e->getCode();
            $writeErrorMessage = $e->getMessage();
        }

        if ($writeSuccess) {
            $this->tty->info('Migrations available at:');
            $this->tty->text($this->exportPath);
        } else {
            $this->tty->error('Unable to write migration file.');

            if (isset($writeErrorCode)) {
                $this->tty->error("Error Code: {$writeErrorCode}");
            }

            if (isset($writeErrorMessage)) {
                $this->tty->error("Error Message: {$writeErrorMessage}");
            }
        }

        return $writeSuccess;
    }
}
