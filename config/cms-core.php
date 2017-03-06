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
    | Localization
    |--------------------------------------------------------------------------
    |
    | The CMS will follow the application's configuration for handling
    | localization and determining available locales. You can override
    | those values here.
    |
    */

    'locale' => [

        // Leave null to use application default
        'default' => null,

        // A list of locales available in the CMS.
        // Leave null to use localization package (or fallback locale) settings
        'available' => null,

        // If enabled, will set the locale based on the request (if not session-defined yet)
        'request-based' => true,

        // The default locale to use for translating content.
        // Leave null to use default CMS locale.
        'translation-default' => null,

        // A list of locales available for translating editable content (do not need to overlap with CMS locales).
        // Leave null to use 'available' value.
        'translation-locales' => null,
    ],

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
    | Events
    |--------------------------------------------------------------------------
    |
    | The bindings of events to listeners, to be set up in the
    | EventServiceProvider.
    |
    */

    'events' => [
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
        Czim\CmsCore\Support\Enums\Component::MODULES     => \Czim\CmsCore\Modules\ModuleManager::class,
        Czim\CmsCore\Support\Enums\Component::AUTH        => Czim\CmsAuth\Auth\Authenticator::class,
        Czim\CmsCore\Support\Enums\Component::API         => Czim\CmsCore\Api\ApiCore::class,
        Czim\CmsCore\Support\Enums\Component::MENU        => Czim\CmsCore\Menu\MenuRepository::class,
        Czim\CmsCore\Support\Enums\Component::ACL         => Czim\CmsCore\Auth\AclRepository::class,
        Czim\CmsCore\Support\Enums\Component::NOTIFIER    => Czim\CmsCore\Core\BasicNotifier::class,
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
        Czim\CmsCore\Providers\ModuleManagerServiceProvider::class,
        Czim\CmsCore\Providers\LogServiceProvider::class,
        Czim\CmsCore\Providers\EventServiceProvider::class,
        Czim\CmsCore\Providers\MiddlewareServiceProvider::class,
        Czim\CmsCore\Providers\MigrationServiceProvider::class,
        Czim\CmsCore\Providers\TranslationServiceProvider::class,
        Czim\CmsCore\Providers\ViewServiceProvider::class,
        Czim\CmsAuth\Providers\CmsAuthServiceProvider::class,
        Czim\CmsTheme\Providers\CmsThemeServiceProvider::class,
        //Czim\CmsAuth\Providers\Api\OAuthSetupServiceProvider::class,
        //Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider::class,

        // It is safest to call the route service providers last,
        // since they depend on data provided by modules that
        // should be registered and prepared beforehand.

        Czim\CmsCore\Providers\RouteServiceProvider::class,
        //Czim\CmsCore\Providers\Api\ApiRouteServiceProvider::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Aliases
    |--------------------------------------------------------------------------
    |
    | The CMS can have its own aliases. It is inadvisable to create conflicts
    | with application aliases, but it makes sense to set aliases that
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
    | to set global/group middleware. Note that this should only include
    | web middleware -- the API has its own middleware & group.
    |
    */

    'middleware' => [

        // You can also disable all CMS middleware entirely, if you are feeling
        // particularly adventurous. This also disables API middleware.
        'enabled' => true,

        // The middleware group that all CMS routes should belong to.
        'group' => 'cms',

        // The middleware that should be loaded. Middleware without keys will
        // be added for the CMS middleware group.
        'load' => [
            Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
            Illuminate\Cookie\Middleware\EncryptCookies::class,
            Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            Illuminate\Session\Middleware\StartSession::class,
            Illuminate\View\Middleware\ShareErrorsFromSession::class,
            Czim\CmsCore\Http\Middleware\VerifyCsrfToken::class,
            Czim\CmsCore\Http\Middleware\InitializeMenu::class,
            Czim\CmsCore\Http\Middleware\SetLocale::class,

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

        'locale-switch' => 'cms::layout.partials.locale-switch',
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
            'route:*',
            'tinker',
            'vendor:*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Translations
    |--------------------------------------------------------------------------
    |
    | Translations may be namespaced for the CMS. If not found within the
    | prefixed namespace, translations will fall back to the application's.
    |
    */

    'translation' => [

        // The namespace prefix to use for CMS translations (cms_trans())
        'prefix' => 'cms::',
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

        // Whether to write to a separate log file for the CMS.
        // If this is set to false, the standard laravel log will be used.
        'separate' => true,

        // If using a separate log, the (path or) file to write to,
        // relative to the storage/logs directory.
        'file' => 'cms.log',

        // Whether to split files over days, rather than use a single log file.
        'daily' => true,

        // If set, limits that amount of (daily) files kept as history by the logger.
        'max_files' => null,

        // Minimal log level to write log lines for
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

        // List of exception FQNs which should not be reported for the CMS
        'dont-report' => [
            League\OAuth2\Server\Exception\AccessDeniedException::class,
            League\OAuth2\Server\Exception\InvalidClientException::class,
            League\OAuth2\Server\Exception\InvalidCredentialsException::class,
            League\OAuth2\Server\Exception\InvalidGrantException::class,
            League\OAuth2\Server\Exception\InvalidRequestException::class,
            League\OAuth2\Server\Exception\InvalidRefreshException::class,
            League\OAuth2\Server\Exception\InvalidScopeException::class,
            League\OAuth2\Server\Exception\OAuthException::class,
            League\OAuth2\Server\Exception\UnauthorizedClientException::class,
            League\OAuth2\Server\Exception\UnsupportedGrantTypeException::class,
            League\OAuth2\Server\Exception\UnsupportedResponseTypeException::class,
            LucaDegasperi\OAuth2Server\Exceptions\NoActiveAccessTokenException::class,
        ],
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
