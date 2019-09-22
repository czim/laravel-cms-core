<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Contracts\Support\View\AssetManagerInterface;
use Czim\CmsCore\Core\Core;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Exception;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Session\SessionManager;
use Mockery;
use Psr\Log\LoggerInterface;

class CoreTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_returns_its_version()
    {
        $core = $this->makeCore();

        static::assertEquals($core->version(), $core::VERSION);
    }

    /**
     * @test
     */
    function it_returns_the_bound_bootchecker_component()
    {
        $instance = Mockery::mock(BootCheckerInterface::class);
        $this->app->instance(Component::BOOTCHECKER, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->bootChecker());
    }

    /**
     * @test
     */
    function it_returns_the_bound_auth_component()
    {
        $instance = Mockery::mock(AuthenticatorInterface::class);
        $this->app->instance(Component::AUTH, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->auth());
    }

    /**
     * @test
     */
    function it_returns_the_bound_api_component()
    {
        $instance = Mockery::mock(ApiCoreInterface::class);
        $this->app->instance(Component::API, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->api());
    }

    /**
     * @test
     */
    function it_returns_the_bound_cache_component()
    {
        $instance = Mockery::mock(CacheInterface::class);
        $this->app->instance(Component::CACHE, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->cache());
    }

    /**
     * @test
     */
    function it_returns_the_bound_module_manager_component()
    {
        $instance = Mockery::mock(ModuleManagerInterface::class);
        $this->app->instance(Component::MODULES, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->modules());
    }

    /**
     * @test
     */
    function it_returns_the_bound_menu_component()
    {
        $instance = Mockery::mock(MenuRepositoryInterface::class);
        $this->app->instance(Component::MENU, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->menu());
    }

    /**
     * @test
     */
    function it_returns_the_bound_assets_component()
    {
        $instance = Mockery::mock(AssetManagerInterface::class);
        $this->app->instance(Component::ASSETS, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->assets());
    }

    /**
     * @test
     */
    function it_returns_the_bound_acl_component()
    {
        $instance = Mockery::mock(AclRepositoryInterface::class);
        $this->app->instance(Component::ACL, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->acl());
    }

    /**
     * @test
     */
    function it_returns_the_bound_notifier_component()
    {
        $instance = Mockery::mock(NotifierInterface::class);
        $this->app->instance(Component::NOTIFIER, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->notifier());
    }

    /**
     * @test
     */
    function it_returns_the_database_connection_with_the_configured_cms_driver()
    {
        $this->app['config']->set('cms-core.database.driver', 'cms-connection-test');

        /** @var Mockery\Mock|Mockery\MockInterface|ConnectionResolverInterface */
        $dbMock = Mockery::mock(ConnectionResolverInterface::class);
        $dbMock->shouldReceive('connection')->once()
            ->with('cms-connection-test')->andReturnSelf();

        $this->app->instance('db', $dbMock);

        $core = $this->makeCore();

        static::assertEquals($dbMock, $core->db());
    }

    /**
     * @test
     */
    function it_returns_the_session_store_with_the_configured_cms_store()
    {
        $this->app['config']->set('cms-core.session.driver', 'cms-driver-test');

        /** @var SessionManager|Mockery\Mock|Mockery\MockInterface $sessionMock */
        $sessionMock = Mockery::mock(SessionManager::class);

        $sessionMock->shouldReceive('driver')->once()
            ->with('cms-driver-test')->andReturnSelf();

        $this->app->instance('session', $sessionMock);

        $core = $this->makeCore();

        static::assertEquals($sessionMock, $core->session());
    }

    // ------------------------------------------------------------------------------
    //      Config
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_core_configuration_values()
    {
        $this->app['config']->set('cms-core.core-test-key', 'test-value');

        static::assertEquals('test-value', $this->makeCore()->config('core-test-key'));
    }
    /**
     * @test
     */
    function it_returns_core_configuration_value_with_default()
    {
        static::assertEquals('test-default', $this->makeCore()->config('totally-unset-test-key', 'test-default'));
    }

    /**
     * @test
     */
    function it_returns_module_configuration_values()
    {
        $this->app['config']->set('cms-modules.core-test-key', 'test-value');

        static::assertEquals('test-value', $this->makeCore()->moduleConfig('core-test-key'));
    }
    /**
     * @test
     */
    function it_returns_module_configuration_value_with_default()
    {
        static::assertEquals('test-default', $this->makeCore()->moduleConfig('totally-unset-test-key', 'test-default'));
    }

    /**
     * @test
     */
    function it_returns_api_configuration_values()
    {
        $this->app['config']->set('cms-api.core-test-key', 'test-value');

        static::assertEquals('test-value', $this->makeCore()->apiConfig('core-test-key'));
    }
    /**
     * @test
     */
    function it_returns_api_configuration_value_with_default()
    {
        static::assertEquals('test-default', $this->makeCore()->apiConfig('totally-unset-test-key', 'test-default'));
    }


    // ------------------------------------------------------------------------------
    //      Logging
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_the_bound_logger_component_if_no_parameters_are_given()
    {
        $instance = Mockery::mock(LoggerInterface::class);
        $this->app->instance(Component::LOG, $instance);

        $core = $this->makeCore();

        static::assertSame($instance, $core->log());
    }

    /**
     * @test
     */
    function it_logs_given_all_parameters()
    {
        $loggerMock = $this->makeLoggerMock('debug', 'test message', ['test' => 'context']);
        $this->app->instance(Component::LOG, $loggerMock);

        $logger = $this->makeCore()->log('debug', 'test message', ['test' => 'context']);

        self::assertSame($loggerMock, $logger);
    }

    /**
     * @test
     */
    function it_logs_given_null_level_with_message_and_context_defaulting_to_level_info()
    {
        $loggerMock = $this->makeLoggerMock('info', 'test message', ['test' => 'context']);
        $this->app->instance(Component::LOG, $loggerMock);

        $this->makeCore()->log(null, 'test message', ['test' => 'context']);
    }

    /**
     * @test
     */
    function it_logs_given_message_and_context_defaulting_to_level_info()
    {
        $loggerMock = $this->makeLoggerMock('info', 'test message', ['test' => 'context']);
        $this->app->instance(Component::LOG, $loggerMock);

        $this->makeCore()->log('test message', ['test' => 'context']);
    }

    /**
     * @test
     */
    function it_logs_given_only_context_defaulting_to_level_info_with_data_message()
    {
        $loggerMock = $this->makeLoggerMock('info', 'Data.', ['test' => 'context']);
        $this->app->instance(Component::LOG, $loggerMock);

        $this->makeCore()->log(['test' => 'context']);
    }

    /**
     * @test
     */
    function it_logs_given_null_level_with_context_array_defaulting_to_level_info_with_data_message()
    {
        $loggerMock = $this->makeLoggerMock('info', 'Data.', ['test' => 'context']);
        $this->app->instance(Component::LOG, $loggerMock);

        $this->makeCore()->log(null, ['test' => 'context']);
    }

    /**
     * @test
     */
    function it_logs_casting_context_parameter_to_array_if_it_is_not_an_array()
    {
        $loggerMock = $this->makeLoggerMock('emergency', 'testing', ['context']);
        $this->app->instance(Component::LOG, $loggerMock);

        $this->makeCore()->log('emergency', 'testing', 'context');
    }

    /**
     * @test
     */
    function it_logs_exceptions_with_level_error()
    {
        $exception = new Exception('test');

        $loggerMock = $this->makeLoggerMock('error', $exception, []);
        $this->app->instance(Component::LOG, $loggerMock);

        $this->makeCore()->log($exception);
    }


    // ------------------------------------------------------------------------------
    //      Routing
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_prefixes_routes_with_the_configured_prefix()
    {
        $this->app['config']->set('cms-core.route.name-prefix', 'test-prefix::');

        static::assertEquals('test-prefix::route-name', $this->makeCore()->prefixRoute('route-name'));
    }

    /**
     * @test
     */
    function it_does_not_duplicate_prefix_for_routes_that_are_already_prefixed()
    {
        $this->app['config']->set('cms-core.route.name-prefix', 'test-prefix::');

        static::assertEquals('test-prefix::route-name', $this->makeCore()->prefixRoute('test-prefix::route-name'));
    }

    /**
     * @test
     */
    function it_prefixes_api_routes_with_the_configured_prefix()
    {
        $this->app['config']->set('cms-api.route.name-prefix', 'test-prefix::');

        static::assertEquals('test-prefix::route-name', $this->makeCore()->prefixApiRoute('route-name'));
    }

    /**
     * @test
     */
    function it_does_not_duplicate_prefix_for_api_routes_that_are_already_prefixed()
    {
        $this->app['config']->set('cms-api.route.name-prefix', 'test-prefix::');

        static::assertEquals('test-prefix::route-name', $this->makeCore()->prefixApiRoute('test-prefix::route-name'));
    }

    /**
     * @test
     */
    function it_returns_prefixed_route()
    {
        $this->app['config']->set('cms-core.route.name-prefix', 'test::');

        /** @var UrlGenerator|Mockery\Mock|Mockery\MockInterface $urlMock */
        $urlMock = Mockery::mock(UrlGenerator::class);

        $urlMock->shouldReceive('route')->once()
            ->with('test::route-test', ['param'], false)
            ->andReturn('test-return-value');

        $this->app->instance('url', $urlMock);

        $core = $this->makeCore();

        static::assertEquals('test-return-value', $core->route('route-test', ['param'], false));
    }

    /**
     * @test
     */
    function it_returns_prefixed_api_route()
    {
        $this->app['config']->set('cms-api.route.name-prefix', 'test::');

        /** @var UrlGenerator|Mockery\Mock|Mockery\MockInterface $urlMock */
        $urlMock = Mockery::mock(UrlGenerator::class);

        $urlMock->shouldReceive('route')->once()
            ->with('test::route-test', ['param'], false)
            ->andReturn('test-return-value');

        $this->app->instance('url', $urlMock);

        $core = $this->makeCore();

        static::assertEquals('test-return-value', $core->apiRoute('route-test', ['param'], false));
    }

    /**
     * @return LoggerInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function makeLoggerMock()
    {
        /** @var Mockery\Mock|Mockery\MockInterface|LoggerInterface $loggerMock */
        $loggerMock = Mockery::mock(LoggerInterface::class);

        $parameters = func_get_args();

        if (count($parameters) > 2) {

            $loggerMock->shouldReceive('log')->once()
                ->with($parameters[0], $parameters[1], $parameters[2]);

        } elseif (count($parameters) > 1) {

            $loggerMock->shouldReceive('log')->once()
                ->with($parameters[0], $parameters[1]);

        } elseif (count($parameters)) {

            $loggerMock->shouldReceive('log')->once()
                ->with($parameters[0]);
        }

        return $loggerMock;
    }

    protected function makeCore(): Core
    {
        return new Core($this->app);
    }

}
