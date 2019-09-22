<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Providers\MiddlewareServiceProvider;
use Czim\CmsCore\Test\Helpers\Http\KernelWithRemoveMiddlewareMethod;
use Czim\CmsCore\Test\Helpers\Http\NullMiddleware;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;
use Mockery;

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

        $checkerMock->shouldReceive('shouldLoadCmsMiddleware')
            ->atLeast()->once()
            ->andReturn(false);

        $checkerMock->shouldReceive('shouldLoadCmsWebMiddleware')->never();

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

        $checkerMock->shouldReceive('shouldLoadCmsMiddleware')
            ->atLeast()->once()
            ->andReturn(true);

        $checkerMock->shouldReceive('shouldLoadCmsWebMiddleware')
            ->atLeast()->once()
            ->andReturn(true);

        $routerMock->shouldReceive('middlewareGroup')
            ->atLeast()->once()
            ->andReturn('web');

        $kernelMock->shouldReceive('hasMiddleware')
            ->atLeast()->once()
            ->with(NullMiddleware::class)
            ->andReturn(false);

        $routerMock->shouldReceive('pushMiddlewareToGroup')
            ->once()
            ->with('cms_test', NullMiddleware::class)
            ->andReturnSelf();

        $routerMock->shouldReceive('aliasMiddleware')
            ->once()
            ->with('test_key', NullMiddleware::class)
            ->andReturnSelf();

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

        $checkerMock->shouldReceive('shouldLoadCmsMiddleware')
            ->atLeast()->once()
            ->andReturn(true);

        $checkerMock->shouldReceive('shouldLoadCmsWebMiddleware')
            ->andReturn(false);

        $checkerMock->shouldReceive('shouldLoadCmsApiMiddleware')
            ->atLeast()->once()
            ->andReturn(true);

        $routerMock->shouldReceive('middlewareGroup')
            ->atLeast()->once()
            ->andReturn('api');

        $kernelMock->shouldReceive('hasMiddleware')
            ->once()
            ->with(NullMiddleware::class)
            ->andReturn(false);

        $routerMock->shouldReceive('pushMiddlewareToGroup')
            ->once()
            ->with('cms_test_api', NullMiddleware::class)
            ->andReturnSelf();

        $routerMock->shouldReceive('aliasMiddleware')
            ->once()
            ->with('test_key_api', NullMiddleware::class)
            ->andReturnSelf();

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

        $checkerMock->shouldReceive('shouldLoadCmsMiddleware')
            ->atLeast()->once()
            ->andReturn(true);

        $checkerMock->shouldReceive('shouldLoadCmsWebMiddleware')
            ->atLeast()->once()
            ->andReturn(true);

        $routerMock->shouldReceive('middlewareGroup');
        $routerMock->shouldReceive('aliasMiddleware');

        $kernelMock->shouldReceive('hasMiddleware')->andReturn(true);
        $kernelMock->shouldReceive('removeGlobalMiddleware')->once();

        $provider = new MiddlewareServiceProvider($this->app);
        $provider->register();
        $provider->boot($routerMock, $kernelMock, $coreMock, $checkerMock);
    }

    /**
     * @return Router|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockRouter()
    {
        return Mockery::mock(Router::class);
    }

    /**
     * @param bool $hasRemoveGlobalMiddleware
     * @return HttpKernel|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockHttpKernel(bool $hasRemoveGlobalMiddleware = false)
    {
        if ($hasRemoveGlobalMiddleware) {
            return Mockery::mock(KernelWithRemoveMiddlewareMethod::class);
        }

        return Mockery::mock(HttpKernel::class);
    }

    /**
     * @return ConsoleKernel|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockConsoleKernel()
    {
        return Mockery::mock(ConsoleKernel::class);
    }

    /**
     * @return CoreInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockCore()
    {
        $mock = Mockery::mock(CoreInterface::class);

        $mock->shouldReceive('config')->andReturnUsing(
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

        $mock->shouldReceive('apiConfig')->andReturnUsing(
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
     * @return BootCheckerInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockBootChecker()
    {
        return Mockery::mock(BootCheckerInterface::class);
    }

}
