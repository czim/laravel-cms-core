<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Controllers;

use Czim\CmsCore\Http\Controllers\DefaultController;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Contracts\View\Factory;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class DefaultControllerTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_returns_a_default_blank_view()
    {
        $controller = new DefaultController($this->app[Component::CORE]);

        /** @var Factory|Mock|MockInterface $viewMock */
        $viewMock = Mockery::mock(Factory::class);

        $viewMock->shouldReceive('make')->with('cms::blank.index', [], [])->andReturn('testing');

        $this->app->instance(Factory::class, $viewMock);

        static::assertEquals('testing', $controller->index());
    }

}
