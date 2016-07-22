<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\StatusCommand;

class MigrateStatusCommand extends StatusCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate:status';
    protected $description = 'Show the status of each migration for the CMS';

    /**
     * Overridden only to set connection with determineConnection().
     *
     * {@inheritdoc}
     */
    public function fire()
    {
        // Set the connection before checking whether the table on it
        // exists! Otherwise, how could the table be found in the correct place?
        $this->migrator->setConnection($this->determineConnection());

        if ( ! $this->migrator->repositoryExists()) {
            return $this->error('No CMS migrations found.');
        }

        if (! is_null($path = $this->input->getOption('path'))) {
            $path = $this->laravel->basePath().'/'.$path;
        } else {
            $path = $this->getMigrationPath();
        }

        $ran = $this->migrator->getRepository()->getRan();

        $migrations = [];

        foreach ($this->getAllMigrationFiles($path) as $migration) {
            $migrations[] = in_array($migration, $ran) ? ['<info>Y</info>', $migration] : ['<fg=red>N</fg=red>', $migration];
        }

        if (count($migrations) > 0) {
            $this->table(['Ran?', 'CMS Migration'], $migrations);
        } else {
            $this->error('No CMS migrations found');
        }

        return null;
    }

}
