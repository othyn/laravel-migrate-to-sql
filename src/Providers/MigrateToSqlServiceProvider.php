<?php

namespace Othyn\MigrateToSql\Providers;

use Illuminate\Support\ServiceProvider;
use Othyn\MigrateToSql\Console\MigrateToSql;

/**
 * Time to tell Laravel about the package!
 */
class MigrateToSqlServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // Register the new migrate command
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateToSql::class,
            ]);
        }
    }
}
