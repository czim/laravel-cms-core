<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Console;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Test\CmsBootTestCase;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class CacheMenuTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_caches_using_the_menu_repository()
    {
        /** @var Mock|MockInterface|MenuRepositoryInterface $repositoryMock */
        $repositoryMock = Mockery::mock(MenuRepositoryInterface::class);

        $repositoryMock->shouldReceive('clearCache')->once()->andReturnSelf();
        $repositoryMock->shouldReceive('writeCache')->once()->andReturnSelf();

        $this->app->instance(MenuRepositoryInterface::class, $repositoryMock);

        $this->artisan('cms:menu:cache')->assertExitCode(0);
    }

}
