<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\MigrateCommand as LaravelMigrateCommand;

class MigrateRollbackCommand extends LaravelMigrateCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate:rollback';
    protected $description = 'Rollback the last CMS database migration';

    /**
     * Overridden for setConnection only.
     *
     * {@inheritdoc}
     */
    public function fire()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->migrator->setConnection($this->determineConnection());

        $pretend = $this->input->getOption('pretend');

        $this->migrator->rollback($pretend);

        // Once the migrator has run we will grab the note output and send it out to
        // the console screen, since the migrator itself functions without having
        // any instances of the OutputInterface contract passed into the class.
        foreach ($this->migrator->getNotes() as $note) {
            $this->output->writeln($note);
        }
    }

}
