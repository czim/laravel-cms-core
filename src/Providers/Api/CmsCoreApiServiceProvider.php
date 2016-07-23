<?php
namespace Czim\CmsCore\Providers\Api;

use Czim\CmsCore\Support\Enums\Component;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Core\CoreInterface;

class CmsCoreApiServiceProvider extends ServiceProvider
{

    /**
     * @var CoreInterface
     */
    protected $core;


    public function boot()
    {
    }

    public function register()
    {
        $this->core = app(Component::CORE);

        if ( ! $this->core->bootChecker()->shouldCmsApiRegister()) {
            return;
        }

        $this->registerConfiguredServiceProviders();
    }


    /**
     * Registers any service providers listed in the core config.
     *
     * @return $this
     */
    protected function registerConfiguredServiceProviders()
    {
        $providers = $this->core->apiConfig('providers', []);

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        return $this;
    }

}
