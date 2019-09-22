<?php
namespace Czim\CmsCore\Core; // Uses package namespace to allow mocking global functions

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Mockery;

/**
 * Class BootCheckerTest
 *
 * Because of its environment-relative nature, the BootChecker requires
 * bit more 'hacking' to test. See the app() and request() mocks below.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class BootCheckerTest extends TestCase
{

    /**
     * @var callback
     */
    public static $functions;

    /**
     * @var Request|Mockery\Mock
     */
    public static $request;


    protected $environment = 'production';
    protected $runningInConsole = false;

    protected $configCmsEnabled = true;
    protected $configCmsTesting = false;
    protected $configCmsMiddleware = true;
    protected $configWebRoutePrefix = 'cms';

    protected $mockArtisanBaseCommand;
    protected $mockWebRoute = 'cms/test';


    // ------------------------------------------------------------------------------
    //      Registration
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_reports_the_cms_not_should_be_registered_if_disabled()
    {
        $this->configCmsEnabled = false;

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered_for_unit_test()
    {
        $this->configCmsTesting = true;

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registerd_for_the_base_artisan_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = '';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered_for_cms_console_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = 'cms:menu:show';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_api_should_be_registered_for_cms_console_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = 'cms:menu:show';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsApiRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_not_be_registered_for_a_non_cms_console_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = 'some:other:command';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered_for_cms_web_route()
    {
        $this->mockWebRoute = 'cms/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered_for_cms_web_route_with_more_segments_to_match()
    {
        $this->mockWebRoute         = 'cms/deeper/level/url';
        $this->configWebRoutePrefix = 'cms/deeper/level';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered_for_cms_api_route()
    {
        $this->mockWebRoute = 'cms-api/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_not_be_registered_for_a_non_cms_web_or_api_route()
    {
        $this->mockWebRoute = 'non/cms/url';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_api_should_be_registered_for_cms_api_route()
    {
        $this->mockWebRoute = 'cms-api/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldCmsApiRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_api_should_not_be_registered_for_a_non_cms_api_route()
    {
        $this->mockWebRoute = 'cms/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldCmsApiRegister());
    }

    /**
     * @test
     */
    function it_marks_andreports_the_cms_as_registered()
    {
        $this->mockWebRoute = 'cms/testing/url';
        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->isCmsRegistered());

        $checker->markCmsRegistered();

        static::assertTrue($checker->isCmsRegistered());

        $checker->markCmsRegistered(false);

        static::assertFalse($checker->isCmsRegistered());
    }

    // ------------------------------------------------------------------------------
    //      Booting
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_reports_the_cms_should_not_booted_if_it_was_not_registered()
    {
        $this->configCmsEnabled = false;

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldCmsRegister());
        static::assertFalse($checker->shouldCmsBoot());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_boot_if_it_was_marked_registered()
    {
        $checker = $this->getSetUpChecker();

        $checker->markCmsRegistered();

        static::assertTrue($checker->shouldCmsBoot());
    }

    /**
     * @test
     */
    function it_marks_andreports_the_cms_as_booted()
    {
        $this->mockWebRoute = 'cms/testing/url';
        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->isCmsBooted());

        $checker->markCmsBooted();

        static::assertTrue($checker->isCmsBooted());

        $checker->markCmsBooted(false);

        static::assertFalse($checker->isCmsBooted());
    }


    // ------------------------------------------------------------------------------
    //      Middleware
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_reports_the_cms_should_load_middleware_for_cms_web_route()
    {
        $this->mockWebRoute = 'cms/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldLoadCmsMiddleware());
        static::assertTrue($checker->shouldLoadCmsWebMiddleware());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_not_load_any_middleware_for_console_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = 'cms:menu:show';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldLoadCmsMiddleware());
        static::assertFalse($checker->shouldLoadCmsWebMiddleware());
        static::assertFalse($checker->shouldLoadCmsApiMiddleware());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_not_load_middleware_if_explicitly_disabled_in_config()
    {
        $this->configCmsMiddleware = false;
        $this->mockWebRoute        = 'cms/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldLoadCmsMiddleware());
        static::assertFalse($checker->shouldLoadCmsWebMiddleware());
        static::assertFalse($checker->shouldLoadCmsApiMiddleware());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_load_api_middleware_for_cms_api_route()
    {
        $this->mockWebRoute = 'cms-api/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldLoadCmsMiddleware());
        static::assertTrue($checker->shouldLoadCmsApiMiddleware());
        static::assertFalse($checker->shouldLoadCmsWebMiddleware());
    }


    // ------------------------------------------------------------------------------
    //      Routes
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_reports_the_cms_should_not_load_api_middleware_if_explicitly_disabled_in_config()
    {
        $this->configCmsMiddleware = false;
        $this->mockWebRoute        = 'cms-api/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldLoadCmsApiMiddleware());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_register_web_routes_for_cms_web_route()
    {
        $this->mockWebRoute = 'cms/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldRegisterCmsWebRoutes());
        static::assertFalse($checker->shouldRegisterCmsApiRoutes());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_register_api_routes_for_cms_api_route()
    {
        $this->mockWebRoute = 'cms-api/testing/url';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldRegisterCmsApiRoutes());
        static::assertFalse($checker->shouldRegisterCmsWebRoutes());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_register_all_routes_for_cms_console_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = 'cms:menu:show';

        $checker = $this->getSetUpChecker();

        static::assertTrue($checker->shouldRegisterCmsWebRoutes());
        static::assertTrue($checker->shouldRegisterCmsApiRoutes());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_not_register_any_routes_for_non_cms_route()
    {
        $this->mockWebRoute = 'non/cms/url';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldRegisterCmsWebRoutes());
        static::assertFalse($checker->shouldRegisterCmsApiRoutes());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_not_register_any_routes_for_non_cms_command()
    {
        $this->runningInConsole       = true;
        $this->mockArtisanBaseCommand = 'not:cms:command';

        $checker = $this->getSetUpChecker();

        static::assertFalse($checker->shouldRegisterCmsWebRoutes());
        static::assertFalse($checker->shouldRegisterCmsApiRoutes());
    }



    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return BootChecker
     */
    protected function getSetUpChecker()
    {
        $this->setUpConfig();

        self::$functions = $this->getContainerMockCallback();
        self::$request   = $this->getMockRequest();

        if (null !== $this->mockArtisanBaseCommand) {

            /** @var BootChecker|\Mockery\Mock $checker */
            $checker = Mockery::mock(BootChecker::class . '[getArtisanBaseCommand]')->shouldAllowMockingProtectedMethods();
            $checker->shouldReceive('getArtisanBaseCommand')->andReturn($this->mockArtisanBaseCommand);

            return $checker;
        }

        return new BootChecker;
    }

    /**
     * @param Container|\Mockery\MockInterface|null $mock
     * @return \Closure
     */
    protected function getContainerMockCallback($mock = null)
    {
        $mock = $mock ?: $this->getMockContainer();

        return function ($make, $parameters = []) use ($mock) {
            if (is_null($make)) {
                return $mock;
            }

            return $mock->make($make, $parameters);
        };
    }

    /**
     * @return Container|Mockery\MockInterface
     */
    protected function getMockContainer()
    {
        /** @var Mockery\Mock $mock */
        $mock = Mockery::mock(Container::class);

        $mock->shouldReceive('runningInConsole')->andReturn($this->runningInConsole);
        $mock->shouldReceive('runningUnitTests')->andReturn(false);
        $mock->shouldReceive('environment')->andReturn($this->environment);

        return $mock;
    }

    /**
     * @return Request|Mockery\MockInterface
     */
    protected function getMockRequest()
    {
        /** @var Mockery\Mock $mock */
        $mock = Mockery::mock(Request::class);

        $mock->shouldReceive('segment')->withAnyArgs()->andReturnUsing(function ($index) {
            return Arr::get(explode('/', $this->mockWebRoute), ((int) $index) - 1);
        });

        return $mock;
    }

    /**
     * @return $this
     */
    protected function setUpConfig()
    {
        $this->app['config']->set('cms-core.enabled', $this->configCmsEnabled);
        $this->app['config']->set('cms-core.testing', $this->configCmsTesting);
        $this->app['config']->set('cms-core.middleware.enabled', $this->configCmsMiddleware);

        $this->app['config']->set('cms-core.artisan.patterns', ['cms:*']);
        $this->app['config']->set('cms-core.route.prefix', $this->configWebRoutePrefix);
        $this->app['config']->set('cms-api.route.prefix', 'cms-api');

        return $this;
    }

    /**
     * @return BootCheckerInterface
     */
    protected function getBootChecker()
    {
        return app(Component::BOOTCHECKER);
    }

}


// ------------------------------------------------------------------------------
//      Global function mocks
// ------------------------------------------------------------------------------

/**
 * Mock app() function to fake environment, runningInConsole, etc.
 *
 * Overrides normal app() global function.
 *
 * @param null|string $make
 * @param array       $parameters
 * @return mixed
 */
function app($make = null, $parameters = [])
{
    if ( ! BootCheckerTest::$functions) {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }

    $closure = BootCheckerTest::$functions;

    return $closure($make, $parameters);
}

/**
 * Mock request() function to fake simple request checks.
 *
 * @return mixed
 */
function request()
{
    if ( ! BootCheckerTest::$request) {
        return Container::getInstance()->make('request');
    }

    return BootCheckerTest::$request;
}
