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
    public function handle()
    {
        if (! $this->confirmToProceed()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $this->migrator->setConnection($this->determineConnection());

        if ( ! $this->migrator->repositoryExists()) {
            $this->comment('Migration table not found.');
            return;
        }

        $this->migrator->reset(
            $this->getMigrationPaths(), $this->option('pretend')
        );

        // Once the migrator has run we will grab the note output and send it out to
        // the console screen, since the migrator itself functions without having
        // any instances of the OutputInterface contract passed into the class.
        foreach ($this->migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

}
