<?php
namespace Czim\CmsCore\Test\Support\Data;

use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceMode;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;
use Symfony\Component\Translation\TranslatorInterface;

class MenuPresenceTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_returns_id()
    {
        $data = new MenuPresence;
        $data->id = 'test';

        static::assertEquals('test', $data->id());
    }

    /**
     * @test
     */
    function it_returns_type()
    {
        $data = new MenuPresence;
        $data->type = 'test';

        static::assertEquals('test', $data->type());
    }

    /**
     * @test
     */
    function it_returns_action()
    {
        $data = new MenuPresence;
        $data->action = 'test';

        static::assertEquals('test', $data->action());
    }

    /**
     * @test
     */
    function it_returns_parameters()
    {
        $data = new MenuPresence;
        $data->parameters = ['test'];

        static::assertEquals(['test'], $data->parameters());
    }

    /**
     * @test
     */
    function it_returns_permissions()
    {
        $data = new MenuPresence;
        $data->permissions = ['test'];

        static::assertEquals(['test'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array_for_non_array_value()
    {
        $data = new MenuPresence;
        $data->permissions = 'test';

        static::assertEquals(['test'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array_for_empty_value()
    {
        $data = new MenuPresence;
        $data->permissions = null;

        static::assertEquals([], $data->permissions());
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

        $data = new MenuPresence;
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

        $data = new MenuPresence;
        $data->label            = 'Untranslated';
        $data->label_translated = 'translation_key';

        static::assertEquals('Untranslated', $data->label());
    }

    /**
     * @test
     */
    function it_returns_translation_key_for_label()
    {
        $data = new MenuPresence();
        $data->label_translated = 'test';

        static::assertEquals('test', $data->translationKey());
    }

    /**
     * @test
     */
    function it_returns_icon()
    {
        $data = new MenuPresence();
        $data->icon = 'test';

        static::assertEquals('test', $data->icon());
    }

    /**
     * @test
     */
    function it_returns_mode()
    {
        $data = new MenuPresence();
        $data->mode = MenuPresenceMode::ADD;

        static::assertEquals(MenuPresenceMode::ADD, $data->mode());
    }

    /**
     * @test
     */
    function it_returns_mode_modify_by_default()
    {
        $data = new MenuPresence();

        static::assertEquals(MenuPresenceMode::MODIFY, $data->mode());
    }

    /**
     * @test
     */
    function it_return_explicit_keys()
    {
        $data = new MenuPresence();
        $data->explicit_keys = ['test'];

        static::assertEquals(['test'], $data->explicitKeys());
    }

    /**
     * @test
     */
    function it_sets_available_explicitly_keys_only_through_setter()
    {
        $data = new MenuPresence();

        static::assertSame($data, $data->setExplicitKeys(['action', 'icon', 'unknown_key']));

        static::assertEquals(['action', 'icon'], $data->explicitKeys());
    }


    // ------------------------------------------------------------------------------
    //      Children
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_returns_children()
    {
        $data = new MenuPresence;
        $data->children = ['test'];

        static::assertEquals(['test'], $data->children());
    }

    /**
     * @test
     */
    function it_returns_children_as_array_for_non_array_value()
    {
        $data = new MenuPresence;
        $data->children = 'test';

        static::assertEquals(['test'], $data->children());
    }

    /**
     * @test
     */
    function it_returns_children_as_array_for_empty_value()
    {
        $data = new MenuPresence;
        $data->children = null;

        static::assertEquals([], $data->children());
    }

    /**
     * @test
     */
    function it_returns_children_as_collection_if_explicitly_set_as_collection()
    {
        $data = new MenuPresence;
        $data->children = new Collection;

        static::assertInstanceOf(Collection::class, $data->children());
    }

    /**
     * @test
     */
    function it_clears_set_children()
    {
        $data = new MenuPresence();
        $data->children = ['test'];

        $data->clearChildren();

        static::assertEquals([], $data->children);

        // Make sure it stays a collection if it was one
        $data->children = new Collection(['test']);

        $data->clearChildren();

        static::assertInstanceOf(Collection::class, $data->children);
        static::assertTrue($data->children->isEmpty());
    }

    /**
     * @test
     */
    function it_sets_children()
    {
        $data = new MenuPresence();

        $data->setChildren(['test']);

        static::assertEquals(['test'], $data->children());
    }

    /**
     * @test
     */
    function it_adds_a_child_to_the_bottom()
    {
        $data = new MenuPresence();
        $data->children = ['test-a'];

        $child = new MenuPresence([
            'id'     => 'test-b',
            'label'  => 'Test',
            'action' => 'test-action',
        ]);

        $data->addChild($child);

        static::assertCount(2, $data->children());
        static::assertSame($child, array_values($data->children())[1]);
    }

    /**
     * @test
     */
    function it_adds_a_child_to_the_bottom_if_children_is_a_collection()
    {
        $data = new MenuPresence();
        $data->children = new Collection(['test-a']);

        $child = new MenuPresence([
            'id'     => 'test-b',
            'label'  => 'Test',
            'action' => 'test-action',
        ]);

        $data->addChild($child);

        /** @var Collection $children */
        $children = $data->children();

        static::assertInstanceOf(Collection::class, $children);
        static::assertCount(2, $children);
        static::assertSame($child, $children->last());
    }

    /**
     * @test
     */
    function it_adds_a_child_to_the_top()
    {
        $data = new MenuPresence();
        $data->children = ['test-a'];

        $child = new MenuPresence([
            'id'     => 'test-b',
            'label'  => 'Test',
            'action' => 'test-action',
        ]);

        $data->shiftChild($child);

        static::assertCount(2, $data->children());
        static::assertSame($child, array_values($data->children())[0]);
    }

    /**
     * @test
     */
    function it_adds_a_child_to_the_top_if_children_is_a_collection()
    {
        $data = new MenuPresence();
        $data->children = new Collection(['test-a']);

        $child = new MenuPresence([
            'id'     => 'test-b',
            'label'  => 'Test',
            'action' => 'test-action',
        ]);

        $data->shiftChild($child);

        /** @var Collection $children */
        $children = $data->children();

        static::assertInstanceOf(Collection::class, $children);
        static::assertCount(2, $children);
        static::assertSame($child, $children->first());
    }

}
