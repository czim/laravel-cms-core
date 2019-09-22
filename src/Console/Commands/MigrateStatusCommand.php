<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\StatusCommand;
use Illuminate\Support\Collection;

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
    public function handle(): void
    {
        // Set the connection before checking whether the table on it
        // exists! Otherwise, how could the table be found in the correct place?
        $this->migrator->setConnection($this->determineConnection());

        if ( ! $this->migrator->repositoryExists()) {
            $this->error('No migrations found.');
            return;
        }

        $this->migrator->setConnection($this->option('database'));

        $ran = $this->migrator->getRepository()->getRan();

        $migrations = Collection::make($this->getAllMigrationFiles())
            ->map(function ($migration) use ($ran) {
                return in_array($this->migrator->getMigrationName($migration), $ran, true)
                    ? ['<info>Y</info>', $this->migrator->getMigrationName($migration)]
                    : ['<fg=red>N</fg=red>', $this->migrator->getMigrationName($migration)];
            });

        if (count($migrations) > 0) {
            $this->table(['Ran?', 'CMS Migration'], $migrations);
        } else {
            $this->error('No CMS migrations found');
        }
    }

}
