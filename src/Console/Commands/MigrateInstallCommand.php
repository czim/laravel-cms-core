<?php
namespace Czim\CmsCore\Console\Commands;

use Illuminate\Database\Console\Migrations\InstallCommand;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;

class MigrateInstallCommand extends InstallCommand
{
    use CmsMigrationContextTrait;

    protected $name = 'cms:migrate:install';
    protected $description = 'Create the migration repository for the CMS';

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * Create a new migration install command instance.
     * Overridden to add Core reference.
     *
     * @param MigrationRepositoryInterface $repository
     * @param CoreInterface                $core
     */
    public function __construct(MigrationRepositoryInterface $repository, CoreInterface $core)
    {
        parent::__construct($repository);

        $this->core = $core;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $this->repository->setSource($this->determineConnection());

        $this->repository->createRepository();

        $this->info('CMS migration table created successfully.');
    }

}
