<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable CMS API
    |--------------------------------------------------------------------------
    |
    | The API of the CMS may be disabled separately from the CMS itself.
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

        // Prefix all route names with this
        'name-prefix' => 'cms-api::',

        'auth' => [
            // The login/logut endpoints, f.i. in: /cms-api/auth/<path>
            'path' => [
                'login'  => 'issue',
                'logout' => 'revoke',
            ],
            // The router method to use for the API login/logout actions (f.i. post, get, any)
            'method' => [
                'login'  => 'post',
                'logout' => 'post',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Providers
    |--------------------------------------------------------------------------
    |
    | Services providers that the CMS should load for its API, on top of
    | its own. Note that any module-defined service providers get always
    | get loaded for the web and API equally.
    |
    */

    'providers' => [
        Czim\CmsAuth\Providers\Api\FluentStorageServiceProvider::class,
        Czim\CmsAuth\Providers\Api\OAuth2ServerServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to be active when the CMS API is used may be defined here.
    | Use key-value pairs to add route middleware, or plain FQNs strings
    | to set global/group middleware.
    |
    */

    'middleware' => [

        // The middleware group that all CMS API routes should belong to.
        'group' => 'cms-api',

        // The middleware that should be loaded for the API specifically.
        // Middleware without keys will be added for the CMS API middleware group.
        'load' => [
            Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,

            Czim\CmsCore\Support\Enums\CmsMiddleware::API_AUTHENTICATED =>
                Czim\CmsAuth\Http\Middleware\Api\OAuthMiddleware::class,
            Czim\CmsCore\Support\Enums\CmsMiddleware::API_AUTH_OWNER =>
                Czim\CmsAuth\Http\Middleware\Api\OAuthUserOwnerMiddleware::class,
            Czim\CmsCore\Support\Enums\CmsMiddleware::PERMISSION =>
                Czim\CmsCore\Http\Middleware\CheckPermission::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Builder
    |--------------------------------------------------------------------------
    |
    | The CMS builds API responses through a central converter class, that
    | takes content data and prepares correctly formatted (json) responses.
    |
    */

    'response-builder' => Czim\CmsCore\Api\Response\RestResponseBuilder::class,

    /*
    |--------------------------------------------------------------------------
    | Controllers
    |--------------------------------------------------------------------------
    |
    | Here you can set the controllers that the API should use for its meta
    | information routes.
    |
    */

    'controllers' => [
        'menu'    => Czim\CmsCore\Http\Controllers\Api\MenuController::class,
        'modules' => Czim\CmsCore\Http\Controllers\Api\ModulesController::class,
        'version' => Czim\CmsCore\Http\Controllers\Api\VersionController::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debugging & Testing
    |--------------------------------------------------------------------------
    |
    | For local development, debugging options may be used to change API
    | authentication and exception handling.
    |
    */

    'debug' => [
        // Disable authentication entirely. Note that this will ONLY work in 'local' env.
        'disable-local-auth' => env('CMS_API_DISABLE_AUTH', false),

        // A user may be faked by setting their ID in the header set here.
        // This can only be done locally, when disable-local-auth is set to true.
        'debug-user-header' => 'debug-user',

        // Add debug & trace information about exceptions in local environment
        'local-exception-trace' => true,
    ],

];
