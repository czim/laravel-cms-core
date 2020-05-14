<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Console;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Test\CmsBootTestCase;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class ClearMenuCacheTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_clears_cache_using_the_menu_repository()
    {
        /** @var MenuRepositoryInterface|Mock|MockInterface $repositoryMock */
        $repositoryMock = Mockery::mock(MenuRepositoryInterface::class);

        $repositoryMock->shouldReceive('clearCache')->once();

        $this->app->instance(MenuRepositoryInterface::class, $repositoryMock);

        $this->artisan('cms:menu:clear')->assertExitCode(0);
    }

}
