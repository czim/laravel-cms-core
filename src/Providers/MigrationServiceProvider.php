<?php
namespace Czim\CmsCore\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Console\Commands\MigrateCommand;
use Czim\CmsCore\Console\Commands\MigrateInstallCommand;
use Czim\CmsCore\Console\Commands\MigrateRefreshCommand;
use Czim\CmsCore\Console\Commands\MigrateResetCommand;
use Czim\CmsCore\Console\Commands\MigrateRollbackCommand;
use Czim\CmsCore\Console\Commands\MigrateStatusCommand;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;

class MigrationServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }


    public function register()
    {
        $this->registerCmsMigrator()
             ->registerMigrateCommands();
    }

    /**
     * Registers the Migrator that the CMS should use for its Migrate commands.
     *
     * @return $this
     */
    protected function registerCmsMigrator()
    {
        // Make sure that we do not use the same migrations table if CMS migrations
        // share the database with the app.
        $this->app->singleton('cms.migration-repository', function (Application $app) {

            $table = $this->getCore()->config(
                'database.migrations.table',
                $app['config']['database.migrations']
            );

            return new DatabaseMigrationRepository($app['db'], $table);
        });

        // This CMS migrator uses the exact same binding setup as the normal migrator,
        // except for this table configuration.
        $this->app->singleton('cms.migrator', function ($app) {
            return new Migrator($app['cms.migration-repository'], $app['db'], $app['files']);
        });


        // Bind the migrator contextually, so it will be set for the commands bound here.
        $this->app->when(MigrateCommand::class)
                  ->needs(Migrator::class)
                  ->give('cms.migrator');

        $this->app->when(MigrateInstallCommand::class)
                  ->needs(MigrationRepositoryInterface::class)
                  ->give('cms.migration-repository');

        $this->app->when(MigrateRefreshCommand::class)
                  ->needs(Migrator::class)
                  ->give('cms.migrator');

        $this->app->when(MigrateResetCommand::class)
                  ->needs(Migrator::class)
                  ->give('cms.migrator');

        $this->app->when(MigrateRollbackCommand::class)
                  ->needs(Migrator::class)
                  ->give('cms.migrator');

        $this->app->when(MigrateStatusCommand::class)
                  ->needs(Migrator::class)
                  ->give('cms.migrator');

        return $this;
    }

    /**
     * Register Migration CMS commands
     *
     * @return $this
     */
    protected function registerMigrateCommands()
    {
        $this->app->singleton('cms.commands.db-migrate', MigrateCommand::class);
        $this->app->singleton('cms.commands.db-migrate-install', MigrateInstallCommand::class);
        $this->app->singleton('cms.commands.db-migrate-refresh', MigrateRefreshCommand::class);
        $this->app->singleton('cms.commands.db-migrate-reset', MigrateResetCommand::class);
        $this->app->singleton('cms.commands.db-migrate-rollback', MigrateRollbackCommand::class);
        $this->app->singleton('cms.commands.db-migrate-status', MigrateStatusCommand::class);

        $this->commands([
            'cms.commands.db-migrate',
            'cms.commands.db-migrate-install',
            'cms.commands.db-migrate-reset',
            'cms.commands.db-migrate-refresh',
            'cms.commands.db-migrate-rollback',
            'cms.commands.db-migrate-status',
        ]);

        return $this;
    }

    /**
     * @return CoreInterface
     */
    protected function getCore()
    {
        return $this->app[Component::CORE];
    }

}
