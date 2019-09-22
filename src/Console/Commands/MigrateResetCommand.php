<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\ResetCommand;

class MigrateResetCommand extends ResetCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate:reset';
    protected $description = 'Rollback all CMS database migrations';

    /**
     * Overridden for setConnection only.
     *
     * {@inheritdoc}
     */
    public function handle(): void
    {
        if (! $this->confirmToProceed()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $this->migrator->setConnection($this->determineConnection());

        if (! $this->migrator->repositoryExists()) {
            $this->comment('Migration table not found.');
            return;
        }

        // First, we'll make sure that the migration table actually exists before we
        // start trying to rollback and re-run all of the migrations. If it's not
        // present we'll just bail out with an info message for the developers.
        $this->migrator->setOutput($this->output)->reset(
            $this->getMigrationPaths(), $this->option('pretend')
        );
    }

}
