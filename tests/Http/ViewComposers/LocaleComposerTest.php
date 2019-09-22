<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\ViewComposers\LocaleComposer;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Contracts\View\View;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class LocaleComposerTest extends TestCase
{

    /**
     * @test
     */
    function it_composes_locale_data()
    {
        /** @var LocaleRepositoryInterface|Mock|MockInterface $repositoryMock */
        /** @var View|Mock|MockInterface $viewMock */
        $repositoryMock = Mockery::mock(LocaleRepositoryInterface::class);
        $viewMock       = Mockery::mock(View::class);

        $viewMock->shouldReceive('with')->once()
            ->with([
                'localized'        => true,
                'currentLocale'    => 'en',
                'availableLocales' => ['nl', 'en'],
            ]);

        $repositoryMock->shouldReceive('isLocalized')->once()
            ->andReturn(true);

        $repositoryMock->shouldReceive('getAvailable')->once()
            ->andReturn(['nl', 'en']);

        $composer = new LocaleComposer($repositoryMock);
        $composer->compose($viewMock);
    }

}
