<?php
namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Providers\RouteServiceProvider;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;

/**
 * Class RouteServiceProviderTest
 *
 * This has become quite the beast.
 * Probably better to integration-test this, but for now this will serve.
 */
class RouteServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    function it_registers_cms_routes()
    {
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();
        $authMock    = $this->getMockAuth();
        $modulesMock = $this->getMockModules();

        $coreMock->expects(static::atLeastOnce())->method('auth')->willReturn($authMock);
        $coreMock->expects(static::atLeastOnce())->method('modules')->willReturn($modulesMock);

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldRegisterCmsWebRoutes')
            ->willReturn(true);

        $modulesMock->expects(static::once())->method('mapWebRoutes');

        $provider = new RouteServiceProvider($this->app);
        $provider->boot($this->getElaborateRouterMock(), $coreMock, $checkerMock);
    }

    /**
     * @test
     */
    function it_does_not_register_routes_if_bootchecker_says_not_to()
    {
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();

        $coreMock->expects(static::never())->method('auth');
        $coreMock->expects(static::never())->method('modules');

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldRegisterCmsWebRoutes')
            ->willReturn(false);

        $provider = new RouteServiceProvider($this->app);
        $provider->boot($this->getMockRouter(), $coreMock, $checkerMock);
    }


    /**
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getElaborateRouterMock()
    {
        // The router in the auth closure
        $routerMockAuth = $this->getMockRouter();
        $routerMockAuth->expects(static::atLeastOnce())->method('get');
        $routerMockAuth->expects(static::atLeastOnce())->method('post');

        // The router in the authenticated middleware closure
        $routerMockAuthed = $this->getMockRouter();
        $routerMockAuthed->expects(static::atLeastOnce())->method('get');
        $routerMockAuthed->expects(static::once())
            ->method('group')
            ->with(['prefix' => 'meta'], static::isType('callable'))
            ->willReturnCallback(function () use ($routerMockAuthed) {
                $callable = func_get_arg(1);
                $callable($routerMockAuthed);
            });

        // The router in the first closure
        $routerMockOne = $this->getMockRouter();
        $routerMockOne->expects(static::exactly(2))
            ->method('group')
            ->with(static::isType('array'), static::isType('callable'))
            ->willReturnCallback(function (array $context, callable $callable) use ($routerMockAuth, $routerMockAuthed) {
                // At this level, two groups should be created: the auth group and the main cms group with auth middleware set
                if (Arr::get($context, 'prefix') === 'auth') {
                    $callable($routerMockAuth);
                } else {
                    $callable($routerMockAuthed);
                }
            });

        // Top level router
        $routerMock = $this->getMockRouter();
        $routerMock->expects(static::once())
            ->method('group')
            ->with([
                'prefix'     => 'cms',
                'as'         => 'cms::',
                'middleware' => ['cms'],
            ], static::isType('callable'))
            ->willReturnCallback(function () use ($routerMockOne) {
                $callable = func_get_arg(1);
                $callable($routerMockOne);
            });

        return $routerMock;
    }

    /**
     * @return AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockAuth()
    {
        $authMock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();

        $authMock->expects(static::atLeastOnce())
            ->method('getRouteLoginAction')
            ->willReturn('login');

        $authMock->expects(static::atLeastOnce())
            ->method('getRouteLoginPostAction')
            ->willReturn('loginPost');

        $authMock->expects(static::atLeastOnce())
            ->method('getRouteLogoutAction')
            ->willReturn('logout');

        $authMock->expects(static::atLeastOnce())
            ->method('getRoutePasswordEmailGetAction')
            ->willReturn('email');

        $authMock->expects(static::atLeastOnce())
            ->method('getRoutePasswordEmailPostAction')
            ->willReturn('loginPost');

        $authMock->expects(static::atLeastOnce())
            ->method('getRoutePasswordResetGetAction')
            ->willReturn('reset');

        $authMock->expects(static::atLeastOnce())
            ->method('getRoutePasswordResetPostAction')
            ->willReturn('resetPost');

        return $authMock;
    }

    /**
     * @return ModuleManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockModules()
    {
        return $this->getMockBuilder(ModuleManagerInterface::class)->getMock();
    }

    /**
     * @return Router|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockRouter()
    {
        return $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCore()
    {
        $mock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $mock->method('config')->willReturnCallback(
            function ($key, $default = null) {
                switch ($key) {

                    case 'route.prefix':
                        return 'cms';

                    case 'route.name-prefix':
                        return 'cms::';

                    case 'middleware.group':
                        return 'cms';
                }
                return $default;
            }
        );

        return $mock;
    }

    /**
     * @return BootCheckerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockBootChecker()
    {
        return $this->getMockBuilder(BootCheckerInterface::class)->getMock();
    }

}
