<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Controllers;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\Controllers\LocaleController;
use Czim\CmsCore\Http\Requests\SetLocaleRequest;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Http\RedirectResponse;
use Mockery;

class LocaleControllerTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_sets_an_available_locale()
    {
        /** @var LocaleRepositoryInterface|Mockery\Mock|Mockery\MockInterface $repositoryMock */
        /** @var SetLocaleRequest|Mockery\Mock|Mockery\MockInterface $requestMock */
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $requestMock    = Mockery::mock(SetLocaleRequest::class);

        $repositoryMock->shouldReceive('isAvailable')
            ->atLeast()->once()
            ->with('nl')
            ->andReturn(true);

        $requestMock->shouldReceive('input')
            ->atLeast()->once()
            ->with('locale')
            ->andReturn('nl');

        $controller = new LocaleController($this->app[Component::CORE], $repositoryMock);

        /** @var RedirectResponse $return */
        $return = $controller->setLocale($requestMock);

        static::assertInstanceOf(RedirectResponse::class, $return);

        static::assertEquals('nl', session()->get('cms::locale'));
    }

    /**
     * @test
     */
    function it_redirects_if_locale_to_set_is_unavailable()
    {
        /** @var LocaleRepositoryInterface|Mockery\Mock|Mockery\MockInterface $repositoryMock */
        /** @var SetLocaleRequest|Mockery\Mock|Mockery\MockInterface $requestMock */
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $requestMock    = Mockery::mock(SetLocaleRequest::class);

        $repositoryMock->shouldReceive('isAvailable')
            ->atLeast()->once()
            ->with('nl')
            ->andReturn(false);

        $requestMock->shouldReceive('input')
            ->atLeast()->once()
            ->with('locale')
            ->andReturn('nl');

        $controller = new LocaleController($this->app[Component::CORE], $repositoryMock);

        /** @var RedirectResponse $return */
        $return = $controller->setLocale($requestMock);

        static::assertInstanceOf(RedirectResponse::class, $return);

        static::assertFalse(session()->has('cms::locale'));
    }

}
