<?php
namespace Czim\CmsCore\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->listen = config('cms-core.events', []);
    }

}
