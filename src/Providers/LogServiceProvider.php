<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Support\Logging\CmsLogManager;
use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Support\Enums\Component;

class LogServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }


    public function register()
    {
        if ( ! $this->registerLogger()) {
            // If the logger failed to register,
            // fall back on the default Laravel log manager.
            $this->app->instance(Component::LOG, app('log'));
        }
    }

    /**
     * @return bool
     */
    protected function registerLogger()
    {
        $channel = $this->getConfiguredDefaultChannel();

        if (null === $channel) {
            return false;
        }

        $config = $this->getCmsChannelConfiguration($channel);

        if ( ! is_array($config)) {
            return false;
        }

        $this->defineChannelConfigurationIfNotSet($channel, $config);


        // Make the log manager for the CMS with the given channel as its default.
        $cmsLogManager = new CmsLogManager($this->app, $channel);

        $this->app->instance(Component::LOG, $cmsLogManager);

        return true;
    }

    /**
     * Defines config entry for CMS channel if it does not exist.
     *
     * @param string $channel
     * @param array  $configuration
     */
    protected function defineChannelConfigurationIfNotSet($channel, array $configuration)
    {
        if (Arr::has(config('logging.channels'), $channel)) {
            return;
        }

        config()->set("logging.channels.{$channel}", $configuration);
    }

    /**
     * @return string
     */
    protected function getConfiguredDefaultChannel()
    {
        return config('cms-core.log.channel', 'cms');
    }

    /**
     * @param string $channel
     * @return array
     */
    protected function getCmsChannelConfiguration($channel)
    {
        return config("logging.channels.{$channel}", config('cms-core.log.default'));
    }

}
