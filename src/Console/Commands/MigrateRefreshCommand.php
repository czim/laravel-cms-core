<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\RefreshCommand;

class MigrateRefreshCommand extends RefreshCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate:refresh';
    protected $description = 'Reset and re-run all CMS migrations';

    /**
     * Overridden for database option and artisan command references.
     *
     * {@inheritdoc}
     */
    public function fire()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $database = $this->determineConnection();

        $force = $this->input->getOption('force');

        $path = $this->input->getOption('path');

        $this->call('cms:migrate:reset', [
            '--database' => $database, '--force' => $force,
        ]);

        // The refresh command is essentially just a brief aggregate of a few other of
        // the migration commands and just provides a convenient wrapper to execute
        // them in succession. We'll also see if we need to re-seed the database.
        $this->call('cms:migrate', [
            '--database' => $database,
            '--force' => $force,
            '--path' => $path,
        ]);

        if ($this->needsSeeding()) {
            $this->runSeeder($database);
        }
    }

}
