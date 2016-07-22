<?php
namespace Czim\CmsCore\Providers;

use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\ViewComposers\MenuComposer;

class ViewServiceProvider extends ServiceProvider
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface $core
     */
    public function boot(CoreInterface $core)
    {
        $this->core = $core;

        $this->registerViewComposers();
    }

    public function register()
    {
    }

    /**
     * Registers routes for the entire CMS.
     *
     * @return $this
     */
    protected function registerViewComposers()
    {
        view()->composer(
            $this->core->config('views.menu'),
            MenuComposer::class
        );

        view()->composer(
            $this->core->config('views.top'),
            MenuComposer::class
        );

        return $this;
    }

}
