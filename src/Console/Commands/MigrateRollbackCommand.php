<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\RollbackCommand as LaravelMigrateRollbackCommand;

class MigrateRollbackCommand extends LaravelMigrateRollbackCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate:rollback';
    protected $description = 'Rollback the last CMS database migration';

    /**
     * Overridden for setConnection only.
     *
     * {@inheritdoc}
     */
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $this->migrator->setConnection($this->determineConnection());

        $this->migrator->setOutput($this->output)->rollback(
            $this->getMigrationPaths(), [
                'pretend' => $this->option('pretend'),
                'step' => (int) $this->option('step'),
            ]
        );
    }

}
