<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\ViewComposers\LocaleComposer;
use Illuminate\Support\ServiceProvider;

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
            $this->core->config('views.locale-switch'),
            LocaleComposer::class
        );

        return $this;
    }

}
