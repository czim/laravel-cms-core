<?php
namespace Czim\CmsCore\Console\Commands;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Illuminate\Console\Command;

class CacheMenu extends Command
{

    protected $signature = 'cms:menu:cache';

    protected $description = 'Caches menu data';


    /**
     * Execute the console command.
     *
     * @param MenuRepositoryInterface $repository
     */
    public function handle(MenuRepositoryInterface $repository)
    {
        $repository
            ->clearCache()
            ->writeCache();

        $this->info('CMS menu cached.');
    }

}
