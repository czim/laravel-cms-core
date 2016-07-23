<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable CMS API
    |--------------------------------------------------------------------------
    |
    | The API of the CMS may be disabled separately from the CMS itself
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | The base route prefix to prepend for all CMS API routes, and other global
    | CMS-related route settings. For the CMS Middleware group, that is
    | applied to the whole CMS API route group, see the middleware section.
    |
    */

    'route' => [

        // The route prefix to use for all CMS routes
        'prefix' => 'cms-api',

    ],

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    |
    | Services providers that the CMS should load, on top of its own.
    | Note that normally, modules provide their own service providers,
    | which, through the ModuleManager, get loaded by the core.
    |
    */

    'providers' => [
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to be active when the CMS is used may be defined here.
    | Use key-value pairs to add route middleware, or plain FQNs strings
    | to set global/group middleware.
    |
    */

    'middleware' => [

        // You can also disable all API middleware entirely, if you are feeling
        // particularly adventurous.
        'enabled' => true,

        // The middleware group that all CMS API routes should belong to.
        'group' => 'cms-api',

        // The middleware that should be loaded for the API specifically.
        // Middleware without keys will be added for the CMS API middleware group.
        'load' => [
            Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,

            Czim\CmsCore\Support\Enums\CmsMiddleware::AUTHENTICATED =>
                Czim\CmsCore\Http\Middleware\Api\Authenticate::class,
            Czim\CmsCore\Support\Enums\CmsMiddleware::PERMISSION =>
                Czim\CmsCore\Http\Middleware\CheckPermission::class,

        ],
    ],

];
