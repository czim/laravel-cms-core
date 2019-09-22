<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Http\Middleware\InitializeMenu;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\Request;
use Mockery;

class InitializeMenuTest extends TestCase
{

    /**
     * @test
     */
    function it_initializes_the_menu()
    {
        /** @var CoreInterface|Mockery\Mock|Mockery\MockInterface $coreMock */
        /** @var MenuRepositoryInterface|Mockery\Mock|Mockery\MockInterface $menuMock */
        /** @var Request|Mockery\Mock|Mockery\MockInterface $requestMock */
        $coreMock    = Mockery::mock(CoreInterface::class);
        $menuMock    = Mockery::mock(MenuRepositoryInterface::class);
        $requestMock = Mockery::mock(Request::class);

        $menuMock->shouldReceive('initialize')->once();

        $coreMock->shouldReceive('menu')->atLeast()->once()->andReturn($menuMock);

        $middleware = new InitializeMenu($coreMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

}
