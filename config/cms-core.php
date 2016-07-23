<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable CMS
    |--------------------------------------------------------------------------
    |
    | If the CMS is not enabled, it will not perform service registration
    | or booting at all.
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Routing
    |--------------------------------------------------------------------------
    |
    | The base route prefix to prepend for all CMS routes, and other global
    | CMS-related route settings. For the CMS Middleware group, that is
    | applied to the whole CMS route group, see the middleware section.
    |
    */

    'route' => [

        // The route prefix to use for all CMS routes
        'prefix' => 'cms',

        // Prefix all route names with this
        'name-prefix' => 'cms::',

        // The default view that should be loaded for logged in users
        'default' => Czim\CmsCore\Http\Controllers\DefaultController::class . '@index',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | The database settings that should be used for CMS-only data.
    | The driver may be set, and a prefix that should be used for
    | any CMS-only tables.
    |
    */

    'database' => [
        'driver' => null,
        'prefix' => 'cms_',

        'testing' => [
            // The testing driver is only used if the database driver is non-default.
            // If that is not set above, prefix-only segregation is assumed.
            'driver' => 'cms_testing',
        ],

        'migrations' => [
            // Directory in which the CMS-only migrations are stored (relative to database path)
            'path'  => 'migrations_cms',

            // This only needs to be prefixed if you do not use a separate database connection
            // with a prefix set in the driver.
            'table' => 'cms_migrations',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Bindings
    |--------------------------------------------------------------------------
    |
    | The CMS is set up to be very flexible, and binds its main interfaces
    | and key component names in the service locator, using the classes
    | defined here. Just be sure to implement the correct interfaces.
    |
    */

    // Default (main) module bindings to use
    'bindings' => [
        Czim\CmsCore\Support\Enums\Component::BOOTCHECKER => Czim\CmsCore\Core\BootChecker::class,
        Czim\CmsCore\Support\Enums\Component::CACHE       => Czim\CmsCore\Core\Cache::class,
        Czim\CmsCore\Support\Enums\Component::CORE        => Czim\CmsCore\Core\Core::class,
        Czim\CmsCore\Support\Enums\Component::MODULES     => Czim\CmsCore\Modules\Manager\Manager::class,
        Czim\CmsCore\Support\Enums\Component::AUTH        => Czim\CmsAuth\Auth\Authenticator::class,
        Czim\CmsCore\Support\Enums\Component::API         => Czim\CmsCore\Api\ApiCore::class,
        Czim\CmsCore\Support\Enums\Component::MENU        => Czim\CmsTheme\Menu\Menu::class,
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
        Czim\CmsCore\Providers\LogServiceProvider::class,
        Czim\CmsCore\Providers\RouteServiceProvider::class,
        Czim\CmsCore\Providers\MiddlewareServiceProvider::class,
        Czim\CmsCore\Providers\MigrationServiceProvider::class,
        Czim\CmsCore\Providers\ViewServiceProvider::class,
        Czim\CmsAuth\Providers\CmsAuthServiceProvider::class,
        Czim\CmsTheme\Providers\CmsThemeServiceProvider::class,

        Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider::class,
        Czim\CmsCore\Providers\Api\ApiRouteServiceProvider::class,
        Czim\CmsCore\Providers\Api\OAuthSetupServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases
    |--------------------------------------------------------------------------
    |
    | The CMS can have its own aliases. It is inadvisable to creat conflicts
    | with application aliases, but it makes sense to create aliases that
    | are only available in the CMS context; you can do that here.
    |
    */

    'aliases' => [
        'Cms'      => Czim\CmsCore\Facades\CoreFacade::class,
        'CmsAuth'  => Czim\CmsCore\Facades\AuthenticatorFacade::class,
        'CmsCache' => Czim\CmsCore\Facades\CacheFacade::class,
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

        // You can also disable all CMS middleware entirely, if you are feeling
        // particularly adventurous.
        'enabled' => true,

        // The middleware group that all CMS routes should belong to.
        'group' => 'cms',

        // The middleware that should be loaded. Middleware without keys will
        // be added for the CMS middleware group.
        'load' => [
            Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            Illuminate\Cookie\Middleware\EncryptCookies::class,
            Illuminate\Session\Middleware\StartSession::class,
            Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Czim\CmsCore\Http\Middleware\VerifyCsrfToken::class,

            Czim\CmsCore\Support\Enums\CmsMiddleware::AUTHENTICATED =>
                Czim\CmsCore\Http\Middleware\Authenticate::class,
            Czim\CmsCore\Support\Enums\CmsMiddleware::GUEST =>
                Czim\CmsCore\Http\Middleware\RedirectIfAuthenticated::class,
            Czim\CmsCore\Support\Enums\CmsMiddleware::PERMISSION =>
                Czim\CmsCore\Http\Middleware\CheckPermission::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Views
    |--------------------------------------------------------------------------
    |
    | The CMS uses theming to some extent, but the basic layout components
    | will be structured according to the following view definitions.
    |
    */

    'views' => [
        'layout' => 'cms::layout.default',
        'menu'   => 'cms::layout.menu',
        'top'    => 'cms::layout.top',
        'login'  => 'cms::auth.login',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session
    |--------------------------------------------------------------------------
    |
    | When the CMS uses Session data, it will add the configured prefix to
    | all its keys.
    |
    */

    'session' => [
        'driver' => null,
        'prefix' => 'cms::',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | The CMS has its own cache interface, which may be set to use a
    | specific cache driver. Optionally, default cache tag(s) may be
    | set, as either a string, array or false to disable.
    |
    */

    'cache' => [
        'store' => null,
        'tags'  => 'cms',
    ],

    /*
    |--------------------------------------------------------------------------
    | Artisan
    |--------------------------------------------------------------------------
    |
    | The CMS is not by default enabled for any but its own internal Artisan
    | commands. Add patterns to match commands against to make sure CMS
    | services are available for other commands.
    |
    */

    'artisan' => [
        'patterns' => [
            'cms:*',
            'help',
            'migrate*',
            'route:*',
            'tinker',
            'vendor:*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | The CMS logs information with different conditions to the application
    | itself, if errors are thrown while handling CMS requests or commands.
    |
    */

    'log' => [
        'threshold' => Monolog\Logger::INFO,
    ],

    /*
    |--------------------------------------------------------------------------
    | Exceptions
    |--------------------------------------------------------------------------
    |
    | The CMS can use a custom Exception handler. The default set for this
    | expects there to be a Handler in App\Exceptions\Handler
    |
    | Clean handler:        Czim\CmsCore\Exceptions\Handler::class
    | Extend the App with:  Czim\CmsCore\Exceptions\ExtendingHandler::class
    |
    */

    'exceptions' => [
        'handler' => Czim\CmsCore\Exceptions\Handler::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Debugging & Testing
    |--------------------------------------------------------------------------
    |
    | For local development, debugging may be used to enable logging feedback
    | for low-level CMS functionality. When running unit tests for the CMS,
    | instead of the app, set 'testing' to true.
    |
    */

    'debug' => false,

    'testing' => false,

];
