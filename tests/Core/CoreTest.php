<?php
namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Core\Core;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Session\SessionManager;
use Psr\Log\LoggerInterface;

class CoreTest extends TestCase
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
        $this->app->instance(Component::BOOTCHECKER, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->bootChecker());
    }

    /**
     * @test
     */
    function it_returns_the_bound_auth_component()
    {
        $this->app->instance(Component::AUTH, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->auth());
    }

    /**
     * @test
     */
    function it_returns_the_bound_api_component()
    {
        $this->app->instance(Component::API, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->api());
    }

    /**
     * @test
     */
    function it_returns_the_bound_cache_component()
    {
        $this->app->instance(Component::CACHE, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->cache());
    }

    /**
     * @test
     */
    function it_returns_the_bound_module_manager_component()
    {
        $this->app->instance(Component::MODULES, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->modules());
    }

    /**
     * @test
     */
    function it_returns_the_bound_menu_component()
    {
        $this->app->instance(Component::MENU, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->menu());
    }

    /**
     * @test
     */
    function it_returns_the_bound_acl_component()
    {
        $this->app->instance(Component::ACL, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->acl());
    }

    /**
     * @test
     */
    function it_returns_the_bound_notifier_component()
    {
        $this->app->instance(Component::NOTIFIER, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->notifier());
    }

    /**
     * @test
     */
    function it_returns_the_database_connection_with_the_configured_cms_driver()
    {
        $this->app['config']->set('cms-core.database.driver', 'cms-connection-test');

        $dbMock = $this->getMockBuilder(ConnectionResolverInterface::class)->getMock();
        $dbMock->expects(static::once())
            ->method('connection')
            ->with('cms-connection-test')
            ->willReturnSelf();

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

        $sessionMock = $this->getMockBuilder(SessionManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sessionMock->expects(static::once())
            ->method('driver')
            ->with('cms-driver-test')
            ->willReturnSelf();

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
        $this->app->instance(Component::LOG, 'core-test-string');

        $core = $this->makeCore();

        static::assertEquals('core-test-string', $core->log());
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

        $urlMock = $this->getMockBuilder(UrlGenerator::class)->getMock();

        $urlMock->expects(static::once())
            ->method('route')
            ->with('test::route-test', ['param'], false)
            ->willReturn('test-return-value');

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

        $urlMock = $this->getMockBuilder(UrlGenerator::class)->getMock();

        $urlMock->expects(static::once())
            ->method('route')
            ->with('test::route-test', ['param'], false)
            ->willReturn('test-return-value');

        $this->app->instance('url', $urlMock);

        $core = $this->makeCore();

        static::assertEquals('test-return-value', $core->apiRoute('route-test', ['param'], false));
    }

    /**
     * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function makeLoggerMock()
    {
        $loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();

        $parameters = func_get_args();

        if (count($parameters) > 2) {

            $loggerMock->expects(static::once())->method('log')
                ->with($parameters[0], $parameters[1], $parameters[2]);

        } elseif (count($parameters) > 1) {

            $loggerMock->expects(static::once())->method('log')
                ->with($parameters[0], $parameters[1]);

        } elseif (count($parameters)) {

            $loggerMock->expects(static::once())->method('log')
                ->with($parameters[0]);
        }

        return $loggerMock;
    }

    /**
     * @return Core
     */
    protected function makeCore()
    {
        return new Core($this->app);
    }

}
