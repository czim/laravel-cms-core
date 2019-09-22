<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Support\Data;

use Czim\CmsCore\Test\Helpers\Support\AnotherTestDataObject;
use Czim\CmsCore\Test\Helpers\Support\TestDataObject;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Support\Collection;

/**
 * Class DataObjectTest
 *
 * Test for the abstract dataobject features.
 * @see \Czim\CmsCore\Support\Data\AbstractDataObject
 */
class DataObjectTest extends TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app): void
    {
        // don't boot CMS
    }

    /**
     * @test
     */
    function it_clears_all_attributes()
    {
        $data = new TestDataObject([
            'some' => 'values',
            'that' => 'are',
            'set' => [ '!' ],
        ]);

        $data->clear();

        static::assertNull($data->some);
        static::assertNull($data->that);
        static::assertNull($data->set);
    }


    // ------------------------------------------------------------------------------
    //      Lazy Decoration of nested DataObjects
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_interprets_a_value_as_a_dataobject_for_objects_definition()
    {
        $data = new TestDataObject([
            'test' => [
                'some' => 'values',
                'that' => 'are set',
            ],
        ]);

        $value = $data->test;

        static::assertInstanceOf(TestDataObject::class, $value);
        static::assertEquals('values', $value->some);
        static::assertEquals('are set', $value->that);
    }

    /**
     * @test
     */
    function it_interprets_a_value_as_a_dataobject_for_objects_definition_if_value_is_arrayable()
    {
        $data = new TestDataObject([
            'test' => new Collection([
                'some' => 'values',
                'that' => 'are set',
            ]),
        ]);

        $value = $data->test;

        static::assertInstanceOf(TestDataObject::class, $value);
        static::assertEquals('values', $value->some);
        static::assertEquals('are set', $value->that);
    }

    /**
     * @test
     */
    function it_returns_null_if_value_for_key_in_objects_definition_is_null()
    {
        $data = new TestDataObject([
            'test' => null,
        ]);

        static::assertNull($data->test);
    }

    /**
     * @test
     */
    function it_interprets_a_value_as_a_dataobject_for_objects_definition_when_accessing_as_array()
    {
        $data = new TestDataObject([
            'test' => [
                'some' => 'values',
                'that' => 'are set',
            ],
        ]);

        $value = $data['test'];

        static::assertInstanceOf(TestDataObject::class, $value);
        static::assertEquals('values', $value->some);
        static::assertEquals('are set', $value->that);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_an_objects_definition_value_is_not_an_array_or_arrayable()
    {
        $this->expectException(\UnexpectedValueException::class);

        $data = new TestDataObject([
            'test' => 'test',
        ]);

        $data->test;
    }

    /**
     * @test
     */
    function it_interprets_a_value_as_an_array_of_dataobjects_for_objects_definition()
    {
        $data = new TestDataObject([
            'others' => [
                [
                    'key'   => 'test',
                    'value' => 'a',
                ],
                [
                    'key'   => 'test',
                    'value' => 'b',
                ],
            ],
        ]);

        $value = $data->others;

        static::assertIsArray($value);
        static::assertInstanceOf(AnotherTestDataObject::class, $value[0]);
        static::assertInstanceOf(AnotherTestDataObject::class, $value[1]);
        static::assertEquals('test', $value[0]->key);
        static::assertEquals('a', $value[0]->value);
        static::assertEquals('test', $value[1]->key);
        static::assertEquals('b', $value[1]->value);
    }

    /**
     * @test
     */
    function it_interprets_a_value_as_an_array_of_dataobjects_for_objects_definition_if_value_is_arrayable()
    {
        $data = new TestDataObject([
            'others' => [
                new Collection([
                    'key'   => 'test',
                    'value' => 'a',
                ]),
            ],
        ]);

        $value = $data->others;

        static::assertIsArray($value);
        static::assertInstanceOf(AnotherTestDataObject::class, $value[0]);
        static::assertEquals('test', $value[0]->key);
        static::assertEquals('a', $value[0]->value);
    }

    /**
     * @test
     */
    function it_returns_null_if_value_for_key_in_objects_definition_is_null_for_an_array_of_dataobjects()
    {
        $data = new TestDataObject([
            'others' => [
                [
                    'key'   => 'test',
                    'value' => 'a',
                ],
                null,
            ],
        ]);

        $value = $data->others;

        static::assertIsArray($value);
        static::assertNull($data->others[1]);
    }

    /**
     * @test
     */
    function it_interprets_a_value_as_an_array_of_dataobjects_for_objects_definition_when_accessing_as_array()
    {
        $data = new TestDataObject([
            'others' => [
                [
                    'key'   => 'test',
                    'value' => 'a',
                ],
            ],
        ]);

        $value = $data['others'];

        static::assertIsArray($value);
        static::assertInstanceOf(AnotherTestDataObject::class, $value[0]);
        static::assertEquals('test', $value[0]->key);
        static::assertEquals('a', $value[0]->value);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_an_objects_definition_value_is_not_an_array_for_array_of_dataobjects()
    {
        $this->expectException(\UnexpectedValueException::class);

        $data = new TestDataObject([
            'others' => [
                (object) ['test' => 'fail'],
            ],
        ]);

        $data->others;
    }


    // ------------------------------------------------------------------------------
    //      Merging
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_overwrite_a_scalar_value_merged_with_new_value()
    {
        $data = new TestDataObject([
            'some' => 'keep this',
            'that' => 10,
            'set'  => false,
        ]);

        $data->testMerge('some', 'new');
        $data->testMerge('that', 11);
        $data->testMerge('set', true);

        static::assertEquals('new', $data->some);
        static::assertEquals(11, $data->that);
        static::assertSame(true, $data->set);
    }

    /**
     * @test
     */
    function it_does_not_overwrite_a_value_merged_with_null()
    {
        $data = new TestDataObject([
            'some' => 'keep this',
        ]);

        $data->testMerge('some', null);

        static::assertEquals('keep this', $data->some);
    }

    /**
     * @test
     */
    function it_merges_nested_dataobject_attribute_using_a_merge_method_if_it_is_available_with_dataobject_instance()
    {
        $data = new TestDataObject([
            'other' => [
                'key'   => 'test',
                'value' => 'a',
            ],
        ]);

        $newAnother = new AnotherTestDataObject([
            'key'   => 'new',
            'value' => 'b',
        ]);

        $data->testMerge('other', $newAnother);

        static::assertInstanceOf(AnotherTestDataObject::class, $data->other);
        static::assertEquals('new', $data->other->key);
        static::assertEquals('b', $data->other->value);
    }

    /**
     * @test
     */
    function it_merges_nested_dataobject_attribute_using_a_merge_method_if_it_is_available_with_array()
    {
        $data = new TestDataObject([
            'other' => [
                'key'   => 'test',
                'value' => 'a',
            ],
        ]);

        $newAnother = [
            'key'   => 'new',
            'value' => 'b',
        ];

        $data->testMerge('other', $newAnother);

        static::assertInstanceOf(AnotherTestDataObject::class, $data->other);
        static::assertEquals('new', $data->other->key);
        static::assertEquals('b', $data->other->value);
    }

    /**
     * @test
     */
    function it_merges_nested_dataobject_attribute_using_a_merge_method_without_overwriting_with_null_value()
    {
        $data = new TestDataObject([
            'other' => [
                'key'   => 'test',
                'value' => 'a',
            ],
        ]);

        $data->testMerge('other', null);

        static::assertInstanceOf(AnotherTestDataObject::class, $data->other);
        static::assertEquals('test', $data->other->key);
    }

}
