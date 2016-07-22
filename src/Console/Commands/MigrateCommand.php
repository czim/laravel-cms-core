<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand as LaravelMigrateCommand;

class MigrateCommand extends LaravelMigrateCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate';
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
