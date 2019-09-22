<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Providers\MiddlewareServiceProvider;
use Czim\CmsCore\Test\Helpers\Http\NullMiddleware;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;

class MiddlewareServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    function it_does_not_load_middleware_if_bootchecker_says_not_to()
    {
        $routerMock  = $this->getMockRouter();
        $kernelMock  = $this->getMockHttpKernel();
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsMiddleware')
            ->willReturn(false);

        $checkerMock->expects(static::never())
            ->method('shouldLoadCmsWebMiddleware');

        $provider = new MiddlewareServiceProvider($this->app);
        $provider->register();
        $provider->boot($routerMock, $kernelMock, $coreMock, $checkerMock);
    }

    /**
     * @test
     */
    function it_loads_web_middleware_if_bootchecker_says_to()
    {
        $routerMock  = $this->getMockRouter();
        $kernelMock  = $this->getMockHttpKernel();
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsMiddleware')
            ->willReturn(true);

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsWebMiddleware')
            ->willReturn(true);

        $routerMock->expects(static::atLeastOnce())
            ->method('middlewareGroup')
            ->willReturn('web');

        $kernelMock->expects(static::once())
            ->method('hasMiddleware')
            ->with(NullMiddleware::class)
            ->willReturn(false);

        $routerMock->expects(static::once())
            ->method('pushMiddlewareToGroup')
            ->with('cms_test', NullMiddleware::class)
            ->willReturnSelf();

        $routerMock->expects(static::once())
            ->method('aliasMiddleware')
            ->with('test_key', NullMiddleware::class)
            ->willReturnSelf();

        $provider = new MiddlewareServiceProvider($this->app);
        $provider->register();
        $provider->boot($routerMock, $kernelMock, $coreMock, $checkerMock);
    }

    /**
     * @test
     */
    function it_loads_api_middleware_if_bootchecker_says_to()
    {
        $routerMock  = $this->getMockRouter();
        $kernelMock  = $this->getMockHttpKernel();
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsMiddleware')
            ->willReturn(true);

        $checkerMock->method('shouldLoadCmsWebMiddleware')
            ->willReturn(false);

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsApiMiddleware')
            ->willReturn(true);

        $routerMock->expects(static::atLeastOnce())
            ->method('middlewareGroup')
            ->willReturn('api');

        $kernelMock->expects(static::once())
            ->method('hasMiddleware')
            ->with(NullMiddleware::class)
            ->willReturn(false);

        $routerMock->expects(static::once())
            ->method('pushMiddlewareToGroup')
            ->with('cms_test_api', NullMiddleware::class)
            ->willReturnSelf();

        $routerMock->expects(static::once())
            ->method('aliasMiddleware')
            ->with('test_key_api', NullMiddleware::class)
            ->willReturnSelf();

        $provider = new MiddlewareServiceProvider($this->app);
        $provider->register();
        $provider->boot($routerMock, $kernelMock, $coreMock, $checkerMock);
    }

    /**
     * @test
     */
    function it_calls_remove_global_middleware_on_kernel_if_method_exists()
    {
        $routerMock  = $this->getMockRouter();
        $kernelMock  = $this->getMockHttpKernel(true);
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsMiddleware')
            ->willReturn(true);

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldLoadCmsWebMiddleware')
            ->willReturn(true);

        $kernelMock->expects(static::once())
            ->method('removeGlobalMiddleware');

        $provider = new MiddlewareServiceProvider($this->app);
        $provider->register();
        $provider->boot($routerMock, $kernelMock, $coreMock, $checkerMock);
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
     * @param bool $hasRemoveGlobalMiddleware
     * @return HttpKernel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockHttpKernel($hasRemoveGlobalMiddleware = false)
    {
        $methods = [
            'hasMiddleware',
        ];

        if ($hasRemoveGlobalMiddleware) {
            $methods[] = 'removeGlobalMiddleware';
        }

        $mock = $this->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->setMethods($methods)
            ->getMock();

        return $mock;
    }

    /**
     * @return ConsoleKernel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockConsoleKernel()
    {
        $mock = $this->getMockBuilder(ConsoleKernel::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
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

                    case 'middleware.load':
                        return [
                            NullMiddleware::class,                  // global middleware
                            'test_key' => NullMiddleware::class,    // route middleware
                        ];

                    case 'middleware.group':
                        return 'cms_test';

                }
                return $default;
            }
        );

        $mock->method('apiConfig')->willReturnCallback(
            function ($key, $default = null) {
                switch ($key) {

                    case 'middleware.load':
                        return [
                            NullMiddleware::class,                      // global middleware
                            'test_key_api' => NullMiddleware::class,  // route middleware
                        ];

                    case 'middleware.group':
                        return 'cms_test_api';

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
