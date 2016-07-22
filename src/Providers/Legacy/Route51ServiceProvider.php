<?php
namespace Czim\CmsCore\Providers\Legacy;

use Czim\CmsCore\Providers\RouteServiceProvider;

class Route51ServiceProvider extends RouteServiceProvider
{

    /**
     * Override: no middlewaregroups in Laravel 5.1
     *
     * @return string
     */
    protected function getCmsMiddlewareGroup()
    {
        return null;
    }

}
