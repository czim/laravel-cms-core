<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\Middleware\Authenticate;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class AuthenticateTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_authenticated()
    {
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var MockRequest|Mock|MockInterface $requestMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $requestMock = Mockery::mock(Request::class);

        $authMock->shouldReceive('check')->once()->andReturn(true);

        $middleware = new Authenticate($authMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_redirects_if_not_authenticated_for_non_ajax_request()
    {
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var MockRequest|Mock|MockInterface $requestMock */
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $requestMock = Mockery::mock(MockRequest::class);
        $coreMock    = Mockery::mock(CoreInterface::class);

        $authMock->shouldReceive('check')->once()->andReturn(false);

        $requestMock->shouldReceive('ajax')->andReturn(false);
        $requestMock->shouldReceive('wantsJson')->andReturn(false);

        // Register and fake the redirect route
        $this->app['router']->get('testing/ignore', ['as' => 'testing', 'uses' => 'NoController@nowhere']);
        $coreMock->shouldReceive('prefixRoute')->andReturn('testing');

        $this->app->instance(Component::CORE, $coreMock);

        $middleware = new Authenticate($authMock);

        $next = function ($request) {
            return $request;
        };

        static::assertInstanceOf(RedirectResponse::class, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_returns_unauthorized_response_if_not_authenticated_for_ajax_request()
    {
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var MockRequest|Mock|MockInterface $requestMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $requestMock = Mockery::mock(MockRequest::class);

        $authMock->shouldReceive('check')->once()->andReturn(false);

        $requestMock->shouldReceive('ajax')->andReturn(true);
        $requestMock->shouldReceive('wantsJson')->andReturn(true);

        $middleware = new Authenticate($authMock);

        $next = function ($request) {
            return $request;
        };

        /** @var Response $response */
        $response = $middleware->handle($requestMock, $next);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(401, $response->getStatusCode());
    }

}
