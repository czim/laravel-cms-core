<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Contracts\Modules\ManagerInterface;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Support\Enums\Component;

/**
 * Class ModuleManagerServiceProvider
 *
 * Separate provider to initialize the module manager, to make sure that this happens
 * before the other providers are booted -- expecting module data to be available.
 */
class ModuleManagerServiceProvider extends ServiceProvider
{

    public function boot()
    {
        /** @var ManagerInterface $manager */
        $manager = app(Component::MODULES);

        $manager->initialize();
    }


    public function register()
    {
    }

}
