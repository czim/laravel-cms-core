<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Support\Data;

use Czim\CmsCore\Support\Data\AclPresence;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Contracts\Translation\Translator;
use Mockery;

class AclPresenceTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_returns_id()
    {
        $data = new AclPresence;
        $data->id = 'test';

        static::assertEquals('test', $data->id());
    }

    /**
     * @test
     */
    function it_returns_type()
    {
        $data = new AclPresence;
        $data->type = 'test';

        static::assertEquals('test', $data->type());
    }

    /**
     * @test
     */
    function it_returns_permissions()
    {
        $data = new AclPresence;
        $data->permissions = ['test'];

        static::assertEquals(['test'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array_for_non_array_value()
    {
        $data = new AclPresence;
        $data->permissions = 'test';

        static::assertEquals(['test'], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_permissions_as_array_for_empty_value()
    {
        $data = new AclPresence;
        $data->permissions = null;

        static::assertEquals([], $data->permissions());
    }

    /**
     * @test
     */
    function it_returns_label_translation_if_available()
    {
        $transMock = Mockery::mock(Translator::class);

        $transMock->shouldReceive('has')->andReturn(true);
        $transMock->shouldReceive('get')->once()->andReturn('testing_translated');

        $this->app->instance('translator', $transMock);

        $data = new AclPresence;
        $data->label            = 'Untranslated';
        $data->label_translated = 'translation_key';

        static::assertEquals('testing_translated', $data->label());
    }

    /**
     * @test
     */
    function it_returns_untranslated_label_if_translation_not_available()
    {
        $transMock = Mockery::mock(Translator::class);

        $transMock->shouldReceive('has')->andReturn(false);
        $transMock->shouldReceive('get')->once()->andReturn('translation_key');

        $this->app->instance('translator', $transMock);

        $data = new AclPresence;
        $data->label            = 'Untranslated';
        $data->label_translated = 'translation_key';

        static::assertEquals('Untranslated', $data->label());
    }

    /**
     * @test
     */
    function it_returns_translation_key_for_label()
    {
        $data = new AclPresence();
        $data->label_translated = 'test';

        static::assertEquals('test', $data->translationKey());
    }

    /**
     * @test
     */
    function it_sets_permissions()
    {
        $data = new AclPresence();

        $data->setPermissions(['testing']);

        static::assertEquals(['testing'], $data->permissions);
    }

    /**
     * @test
     */
    function it_removes_a_permission()
    {
        $data = new AclPresence();
        $data->permissions = ['test-a', 'test-b', 'test-c'];

        $data->removePermission('test-b');

        static::assertEquals(['test-a', 'test-c'], array_values($data->permissions));
    }

}
