<?php
namespace Czim\CmsCore\Test\Support\Data\Menu;

use Czim\CmsCore\Support\Data\Menu\LayoutGroupData;
use Czim\CmsCore\Test\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

class LayoutGroupDataTest extends TestCase
{

    /**
     * @test
     */
    function it_returns_id()
    {
        $data = new LayoutGroupData;
        $data->id = 'test';

        static::assertEquals('test', $data->id());
    }

    /**
     * @test
     */
    function it_returns_group_for_type()
    {
        $data = new LayoutGroupData;

        static::assertEquals('group', $data->type());
    }

    /**
     * @test
     */
    function it_returns_children()
    {
        $data = new LayoutGroupData;
        $data->children = ['test'];

        static::assertEquals(['test'], $data->children());
    }

    /**
     * @test
     */
    function it_returns_translation_key_for_label()
    {
        $data = new LayoutGroupData;
        $data->label_translated = 'test';

        static::assertEquals('test', $data->translationKey());
    }

    /**
     * @test
     */
    function it_returns_label_translation_if_available()
    {
        $transMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['has', 'trans', 'transChoice', 'getLocale', 'setLocale'])
            ->getMock();

        $transMock->expects(static::once())->method('trans')->willReturn('testing_translated');

        $this->app->instance('translator', $transMock);

        $data = new LayoutGroupData;
        $data->label            = 'Untranslated';
        $data->label_translated = 'translation_key';

        static::assertEquals('testing_translated', $data->label());
    }

    /**
     * @test
     */
    function it_returns_untranslated_label_if_translation_not_available()
    {
        $transMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['has', 'trans', 'transChoice', 'getLocale', 'setLocale'])
            ->getMock();

        $transMock->expects(static::once())->method('trans')->willReturn('translation_key');

        $this->app->instance('translator', $transMock);

        $data = new LayoutGroupData;
        $data->label            = 'Untranslated';
        $data->label_translated = 'translation_key';

        static::assertEquals('Untranslated', $data->label());
    }

    /**
     * @test
     */
    function it_returns_icon()
    {
        $data = new LayoutGroupData;
        $data->icon = 'test';

        static::assertEquals('test', $data->icon());
    }

    /**
     * @test
     */
    function it_decorates_children_attribute_with_group_data_instances_for_arrays()
    {
        $data = new LayoutGroupData;

        $data->children = [
            [
                'id'    => 'test-group',
                'label' => 'Test',
            ]
        ];

        $children = $data->children();

        static::assertInternalType('array', $children);
        static::assertInstanceOf(LayoutGroupData::class, head($children));
        static::assertEquals('test-group', head($children)['id']);
    }


}
