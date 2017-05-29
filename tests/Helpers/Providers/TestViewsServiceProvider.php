<?php
namespace Czim\CmsCore\Test\Helpers\Providers;

use Illuminate\Support\ServiceProvider;

class TestViewsServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }

    public function register()
    {
        $this->loadViewsFrom(
            realpath(dirname(__DIR__) . '/resources/views'),
            'cms-test'
        );
    }

}
