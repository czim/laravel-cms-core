<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Http\Middleware\RedirectIfAuthenticated;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Mockery;

class RedirectIfAuthenticatedTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_not_authenticated()
    {
        /** @var MenuRepositoryInterface|Mockery\Mock|Mockery\MockInterface $menuMock */
        /** @var Request|Mockery\Mock|Mockery\MockInterface $requestMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $requestMock = Mockery::mock(Request::class);

        $authMock->shouldReceive('check')->once()->andReturn(false);

        $middleware = new RedirectIfAuthenticated($authMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_redirects_if_authenticated_for_non_ajax_request()
    {
        /** @var AuthenticatorInterface|Mockery\Mock|Mockery\MockInterface $menuMock */
        /** @var Request|Mockery\Mock|Mockery\MockInterface $requestMock */
        /** @var CoreInterface|Mockery\Mock|Mockery\MockInterface $coreMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $requestMock = Mockery::mock(Request::class);
        $coreMock    = Mockery::mock(CoreInterface::class);

        $authMock->shouldReceive('check')->once()->andReturn(true);

        // Register and fake the redirect route
        $this->app['router']->get('testing/ignore', ['as' => 'testing', 'uses' => 'NoController@nowhere']);
        $coreMock->shouldReceive('prefixRoute')->andReturn('testing');

        $this->app->instance(Component::CORE, $coreMock);

        $middleware = new RedirectIfAuthenticated($authMock);

        $next = function ($request) {
            return $request;
        };

        static::assertInstanceOf(RedirectResponse::class, $middleware->handle($requestMock, $next));
    }

}
