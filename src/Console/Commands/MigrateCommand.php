<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand as LaravelMigrateCommand;

class MigrateCommand extends LaravelMigrateCommand
{
    use CmsMigrationContextTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:migrate {--database= : The database connection to use.}
                {--force : Force the operation to run when in production.}
                {--path= : The path of migrations files to be executed.}
                {--pretend : Dump the SQL queries that would be run.}
                {--seed : Indicates if the seed task should be re-run.}
                {--step : Force the migrations to be run so they can be rolled back individually.}';

    protected $description = 'Run the database migrations for the CMS';

    /**
     * Overridden to set the CMS connection, if required.
     *
     * {@inheritdoc}
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->determineConnection());

        if ( ! $this->migrator->repositoryExists()) {
            $options = ['--database' => $this->input->getOption('database')];

            $this->call('cms:migrate:install', $options);
        }
    }

}
