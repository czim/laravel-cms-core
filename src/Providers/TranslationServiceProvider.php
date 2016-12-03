<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Support\Translation\CmsTranslator;
use Illuminate\Support\ServiceProvider;

/**
 * Class TranslationServiceProvider
 *
 * Re-registers translator binding with CMS translator that gives precedence
 * to CMS-specific translations, with a fallback to application translations.
 */
class TranslationServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;


    public function boot()
    {
        // Re-register on boot() instead of register(), to make sure it
        // is done after the standard translator is registered.

        $this->app->singleton('translator', function ($app) {

            $trans = new CmsTranslator($app['translation.loader'], $app['config']['app.locale']);

            $trans->setFallback($app['config']['app.fallback_locale']);

            return $trans;
        });
    }

    public function register()
    {
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['translator'];
    }

}
