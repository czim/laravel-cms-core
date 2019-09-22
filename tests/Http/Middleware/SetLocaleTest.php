<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\Middleware\SetLocale;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
use Hamcrest\Matchers;
use Illuminate\Http\Request;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class SetLocaleTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_cms_is_not_localized()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var LocaleRepositoryInterface|Mock|MockInterface $repositoryMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $requestMock    = Mockery::mock(Request::class);

        $repositoryMock->shouldReceive('isLocalized')->once()->andReturn(false);
        $repositoryMock->shouldReceive('isAvailable')->never();

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_applies_a_valid_locale_set_in_session()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var LocaleRepositoryInterface|Mock|MockInterface $repositoryMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $requestMock    = Mockery::mock(Request::class);

        $repositoryMock->shouldReceive('isLocalized')->once()->andReturn(true);
        $repositoryMock
            ->shouldReceive('isAvailable')->once()
            ->with('nl')->andReturn(true);

        $coreMock->shouldReceive('config')
            ->andReturnUsing(function ($key, $default = null) {
                if ($key === 'session.prefix') {
                    return 'cms::';
                }
                return $default;
            });

        session()->put('cms::locale', 'nl');

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));

        static::assertEquals('nl', app()->getLocale(), 'Session locale was not set');
    }

    /**
     * @test
     */
    function it_applies_a_valid_locale_set_in_request()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var LocaleRepositoryInterface|Mock|MockInterface $repositoryMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $requestMock    = Mockery::mock(MockRequest::class);

        $repositoryMock->shouldReceive('isLocalized')->once()->andReturn(true);
        $repositoryMock->shouldReceive('getDefault')->andReturn('nl');
        $repositoryMock->shouldReceive('getAvailable')->andReturn(['nl', 'fr']);

        $requestMock->shouldReceive('getPreferredLanguage')->once()
            ->with(Matchers::nonEmptyArray())
            ->andReturn('fr');

        $coreMock->shouldReceive('config')
            ->andReturnUsing(function ($key, $default = null) {
                switch ($key) {
                    case 'session.prefix':
                        return 'cms::';
                    case 'locale.request-based':
                        return true;
                }
                return $default;
            });

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));

        static::assertEquals('fr', app()->getLocale(), 'Request locale was not set');
    }

    /**
     * @test
     */
    function it_does_not_apply_invalid_session_locale()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var LocaleRepositoryInterface|Mock|MockInterface $repositoryMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $requestMock    = Mockery::mock(Request::class);

        $repositoryMock->shouldReceive('getDefault');

        $repositoryMock->shouldReceive('isLocalized')->once()->andReturn(true);
        $repositoryMock->shouldReceive('isAvailable')->once()
            ->with('nl')
            ->andReturn(false);

        $coreMock->shouldReceive('config')
            ->andReturnUsing(function ($key, $default = null) {
                switch ($key) {
                    case 'session.prefix':
                        return 'cms::';
                    case 'locale.request-based':
                        return false;
                }
                return $default;
            });

        session()->put('cms::locale', 'nl');

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));

        static::assertEquals('en', app()->getLocale(), 'No locale should have been set');
    }

}
