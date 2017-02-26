<?php
namespace Czim\CmsCore\Console\Commands;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Illuminate\Console\Command;

class ClearMenuCache extends Command
{

    protected $signature = 'cms:menu:clear';

    protected $description = 'Clears cached menu data';


    /**
     * Execute the console command.
     *
     * @param MenuRepositoryInterface $repository
     */
    public function handle(MenuRepositoryInterface $repository)
    {
        $repository->clearCache();

        $this->info('Repository cache flushed.');
    }

}
