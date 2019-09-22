<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Menu;

use Czim\CmsCore\Contracts\Menu\MenuModulesInterpreterInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Menu\MenuConfigInterpreter;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class MenuConfigInterpreterTest extends CmsBootTestCase
{

    /**
     * Testing menu modules standard menu presences.
     *
     * @var MenuPresenceInterface[]
     */
    protected $menuModulesStandard = [];

    /**
     * Testing menu layout.
     *
     * @var array
     */
    protected $menuLayout = [];



    /**
     * @test
     */
    function it_interprets_without_a_menu_layout_set()
    {
        $this->menuModulesStandard = $this->getMockModulePresences();

        $interpreter = $this->makeConfigInterpreter();

        $layout = $interpreter->interpretLayout([]);

        static::assertInstanceOf(MenuLayoutDataInterface::class, $layout);
        static::assertCount(4, $layout->layout());
        static::assertEquals(['test-a', 'test-b', 'test-c', 'test-d'], Arr::pluck($layout->layout(), 'id'));
    }

    /**
     * @test
     */
    function it_interprets_a_configured_nested_menu_layout()
    {
        $this->menuModulesStandard = $this->getMockModulePresences();

        $interpreter = $this->makeConfigInterpreter();

        $layout = $interpreter->interpretLayout([
            'group-a' => [
                'id'       => 'group-a',
                'label'    => 'Test Group',
                'children' => [
                    [
                        'id'       => 'group-b',
                        'label'    => 'Nested Group',
                        'children' => [
                            'test-c',
                            'test-b',
                        ],
                    ],
                ],
            ],
            'test-d',
            'test-a',
        ]);

        static::assertInstanceOf(MenuLayoutDataInterface::class, $layout);

        static::assertCount(3, $layout->layout());
        static::assertEquals(['group-a', 'test-d', 'test-a'], Arr::pluck($layout->layout(), 'id'));
        static::assertEquals(['group-b'], Arr::pluck($layout->layout()['group-a']->children(), 'id'));
        static::assertEquals(
            ['test-c', 'test-b'],
            Arr::pluck(head($layout->layout()['group-a']->children())->children(), 'id')
        );
    }

    /**
     * @test
     */
    function it_interprets_a_simple_layout_for_modules_with_multiple_presences()
    {
        $this->menuModulesStandard = $this->getMockModulePresencesWithArrays();

        $interpreter = $this->makeConfigInterpreter();

        $layout = $interpreter->interpretLayout([
            'group-a' => [
                'id'       => 'group-a',
                'label'    => 'Test Group',
                'children' => [
                    'test-b',
                    'test-a',
                ],
            ],
        ]);

        static::assertInstanceOf(MenuLayoutDataInterface::class, $layout);

        static::assertCount(1, $layout->layout());
        static::assertEquals(['group-a'], Arr::pluck($layout->layout(), 'id'));
        static::assertEquals(
            ['test-c', 'test-d', 'test-a', 'test-b'],
            Arr::pluck($layout->layout()['group-a']->children(), 'id')
        );
    }

    /**
     * @test
     */
    function it_filters_empty_groups_from_the_layout()
    {
        $this->menuModulesStandard = $this->getMockModulePresences();

        $interpreter = $this->makeConfigInterpreter();

        $layout = $interpreter->interpretLayout([
            'group-a' => [
                'id'       => 'group-a',
                'label'    => 'Test Group',
                'children' => [
                    'empty-group' => [
                        'id'       => 'group-b',
                        'label'    => 'Nested Group',
                        'children' => [],
                    ],
                ],
            ],
            'test-a',
            'test-b',
            'test-c',
            'test-d',
        ]);

        static::assertInstanceOf(MenuLayoutDataInterface::class, $layout);

        static::assertCount(4, $layout->layout());
        static::assertArrayNotHasKey('group-a', $layout->layout());
    }


    // ------------------------------------------------------------------------------
    //      Exceptions
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_throws_an_exception_if_a_module_key_is_unknown()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp('#\'test-does-not-exist\'#');

        $this->menuModulesStandard = $this->getMockModulePresences();

        $interpreter = $this->makeConfigInterpreter();

        $interpreter->interpretLayout([
            [
                'id'       => 'group-a',
                'label'    => 'Test Group',
                'children' => [
                    'test-does-not-exist',
                ],
            ],
            'test-a',
        ]);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_a_module_key_is_assigned_more_than_once_in_a_layout()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessageRegExp('#\'test-a\'#');

        $this->menuModulesStandard = $this->getMockModulePresences();

        $interpreter = $this->makeConfigInterpreter();

        $interpreter->interpretLayout([
            [
                'id'       => 'group-a',
                'label'    => 'Test Group',
                'children' => [
                    'test-a',
                ],
            ],
            'test-a',
        ]);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_an_incorrect_value_is_present_in_the_layout_tree()
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->menuModulesStandard = $this->getMockModulePresences();

        $interpreter = $this->makeConfigInterpreter();

        $interpreter->interpretLayout([
            [
                'id'       => 'group-a',
                'label'    => 'Test Group',
                'children' => [
                    false,
                ],
            ],
            'test-a',
        ]);
    }



    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return MenuModulesInterpreterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockModulesInterpreter()
    {
        $mock = $this->getMockBuilder(MenuModulesInterpreterInterface::class)->getMock();

        $mock->expects(static::once())
             ->method('interpret')
             ->willReturn($this->getMockModulesData());

        return $mock;
    }

    /**
     * @return MenuConfiguredModulesDataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockModulesData()
    {
        $mock = $this->getMockBuilder(MenuConfiguredModulesDataInterface::class)->getMock();

        $mock->method('standard')->willReturn($this->menuModulesStandard);
        $mock->method('alternative')->willReturn(new Collection);

        return $mock;
    }

    /**
     * @return Collection
     */
    protected function getMockModulePresences()
    {
        return new Collection([
            'test-a' => [
                new MenuPresence([
                    'id'    => 'test-a',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something',
                ]),
            ],
            'test-b' => [
                new MenuPresence([
                    'id'    => 'test-b',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something else',
                ]),
            ],
            'test-c' => [
                new MenuPresence([
                    'id'    => 'test-c',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something else yet',
                ]),
            ],
            'test-d' => [
                new MenuPresence([
                    'id'    => 'test-d',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something other',
                ]),
            ],
        ]);
    }

    /**
     * @return Collection
     */
    protected function getMockModulePresencesWithArrays()
    {
        return new Collection([
            'test-a' => [
                new MenuPresence([
                    'id'    => 'test-a',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something',
                ]),
                new MenuPresence([
                    'id'    => 'test-b',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something else',
                ]),
            ],
            'test-b' => [
                new MenuPresence([
                    'id'    => 'test-c',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something else yet',
                ]),
                new MenuPresence([
                    'id'    => 'test-d',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something other',
                ]),
            ],
        ]);
    }

    /**
     * @return MenuConfigInterpreter()
     */
    protected function makeConfigInterpreter()
    {
        return new MenuConfigInterpreter($this->getMockModulesInterpreter());
    }

}

