<?php
namespace Czim\CmsCore\Test\Support\Data\Menu;

use Czim\CmsCore\Support\Data\Menu\LayoutData;
use Czim\CmsCore\Support\Data\Menu\LayoutGroupData;
use Czim\CmsCore\Test\TestCase;

class LayoutDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_layout()
    {
        $data = new LayoutData;
        $data->layout = ['test'];

        static::assertEquals(['test'], $data->layout());
    }

    /**
     * @test
     */
    function it_returns_empty_array_as_default_for_layout()
    {
        $data = new LayoutData;

        static::assertEquals([], $data->layout());
    }

    /**
     * @test
     */
    function it_forces_empty_array_for_direct_layout_access()
    {
        $data = new LayoutData;

        $data->layout = null;

        static::assertEquals([], $data->layout);
    }

    /**
     * @test
     */
    function it_returns_alternative()
    {
        $data = new LayoutData;
        $data->alternative = ['test'];

        static::assertEquals(['test'], $data->alternative());
    }

    /**
     * @test
     */
    function it_returns_empty_array_as_default_for_alternative()
    {
        $data = new LayoutData;

        static::assertEquals([], $data->alternative());
    }

    /**
     * @test
     */
    function it_sets_layout()
    {
        $data = new LayoutData;

        static::assertSame($data, $data->setLayout(['test']));
        static::assertEquals(['test'], $data->layout);
    }

    /**
     * @test
     */
    function it_sets_alternative()
    {
        $data = new LayoutData;

        static::assertSame($data, $data->setAlternative(['test']));
        static::assertEquals(['test'], $data->alternative);
    }

    /**
     * @test
     */
    function it_decorates_layout_attribute_with_group_data_instances_for_arrays()
    {
        $data = new LayoutData;

        $data->layout = [
            [
                'id'    => 'test-group',
                'label' => 'Test',
            ]
        ];

        $layout = $data->layout();

        static::assertInternalType('array', $layout);
        static::assertInstanceOf(LayoutGroupData::class, head($layout));
        static::assertEquals('test-group', head($layout)['id']);
    }

}
