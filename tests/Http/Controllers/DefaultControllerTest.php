<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Controllers;

use Czim\CmsCore\Http\Controllers\DefaultController;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Contracts\View\Factory;

class DefaultControllerTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_returns_a_default_blank_view()
    {
        $controller = new DefaultController($this->app[Component::CORE]);

        $viewMock = $this->getMockBuilder(Factory::class)->getMock();
        $viewMock->method('make')->with('cms::blank.index')->willReturn('testing');

        $this->app->instance(Factory::class, $viewMock);

        static::assertEquals('testing', $controller->index());
    }

}
