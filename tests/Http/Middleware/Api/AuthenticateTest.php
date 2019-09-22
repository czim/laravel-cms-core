<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware\Api;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Http\Middleware\Api\Authenticate;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
use Hamcrest\Matchers;
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
        /** @var Request|Mock|MockInterface $requestMock */
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
    function it_returns_unauthorized_response_if_not_authenticated_for_ajax_request()
    {
        /** @var ApiCoreInterface|Mock|MockInterface $apiMock */
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var MockRequest|Mock|MockInterface $requestMock */
        $apiMock     = Mockery::mock(ApiCoreInterface::class);
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $requestMock = Mockery::mock(MockRequest::class);

        $authMock->shouldReceive('check')->once()->andReturn(false);
        $requestMock->shouldReceive('ajax')->andReturn(true);
        $requestMock->shouldReceive('wantsJson')->andReturn(true);

        $apiMock->shouldReceive('response')->once()
            ->with(Matchers::anything(), 401)
            ->andReturn('testing-response');

        $this->app->instance(Component::API, $apiMock);

        $middleware = new Authenticate($authMock);

        $next = function ($request) {
            return $request;
        };

        /** @var Response $response */
        $response = $middleware->handle($requestMock, $next);

        static::assertEquals('testing-response', $response);
    }

}
