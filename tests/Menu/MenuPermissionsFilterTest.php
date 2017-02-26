<?php
namespace Czim\CmsCore\Test\Menu;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Czim\CmsCore\Menu\MenuPermissionsFilter;
use Czim\CmsCore\Support\Data\Menu\LayoutData;
use Czim\CmsCore\Support\Data\Menu\PermissionsIndexData;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsCore\Test\TestCase;

class MenuPermissionsFilterTest extends TestCase
{

    // ------------------------------------------------------------------------------
    //      Index Building
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_builds_a_permissions_index_for_groupless_layout()
    {
        $layout = new LayoutData([
            'layout' => [
                new MenuPresence([
                    'id'    => 'test-a',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something',
                    'permissions' => 'permission-a',
                ]),
                new MenuPresence([
                    'id'    => 'test-c',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something',
                ]),
                new MenuPresence([
                    'id'    => 'test-b',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something',
                    'permissions' => 'permission-b',
                ]),
            ]
        ]);

        $filter = new MenuPermissionsFilter();
        $index = $filter->buildPermissionsIndex($layout);

        static::assertInstanceOf(MenuPermissionsIndexDataInterface::class, $index);
        static::assertEmpty($index->index());
        static::assertEquals(['permission-a', 'permission-b'], $index->permissions());
    }

    /**
     * @test
     */
    function it_builds_a_permissions_index_for_single_layer_group_layout()
    {
        $layout = new LayoutData([
            'layout' => [
                // Group with unconditional presences
                'group-a' => new MenuPresence([
                    'id'    => 'group-a',
                    'type'  => MenuPresenceType::GROUP,
                    'label' => 'something',
                    'children' => [
                        new MenuPresence([
                            'id'    => 'test-a',
                            'type'  => MenuPresenceType::ACTION,
                            'label' => 'something',
                        ]),
                    ]
                ]),
                // Group with no unconditional presences
                'group-b' => new MenuPresence([
                    'id'    => 'group-b',
                    'type'  => MenuPresenceType::GROUP,
                    'label' => 'something',
                    'children' => [
                        new MenuPresence([
                            'id'    => 'test-b',
                            'type'  => MenuPresenceType::ACTION,
                            'label' => 'something',
                            'permissions' => 'permission-a',
                        ]),
                        new MenuPresence([
                            'id'    => 'test-c',
                            'type'  => MenuPresenceType::ACTION,
                            'label' => 'something',
                            'permissions' => 'permission-b',
                        ]),
                    ]
                ]),
                // Group with mixed presences
                'group-c' => new MenuPresence([
                    'id'    => 'group-c',
                    'type'  => MenuPresenceType::GROUP,
                    'label' => 'something',
                    'children' => [
                        new MenuPresence([
                            'id'    => 'test-e',
                            'type'  => MenuPresenceType::ACTION,
                            'label' => 'something',
                            'permissions' => 'permission-x',
                        ]),
                        new MenuPresence([
                            'id'    => 'test-f',
                            'type'  => MenuPresenceType::ACTION,
                            'label' => 'something',
                        ]),
                        new MenuPresence([
                            'id'          => 'test-g',
                            'type'        => MenuPresenceType::ACTION,
                            'label'       => 'something',
                            'permissions' => 'permission-y',
                        ]),
                    ]
                ]),
            ]
        ]);

        $filter = new MenuPermissionsFilter();
        $index = $filter->buildPermissionsIndex($layout);

        static::assertInstanceOf(MenuPermissionsIndexDataInterface::class, $index);
        static::assertEquals([
            'Z3JvdXAtYQ==' => [],
            'Z3JvdXAtYg==' => [ 'permission-a', 'permission-b' ]
        ], $index->index());
        static::assertEquals([
            'permission-a',
            'permission-b',
            'permission-x',
            'permission-y',
        ], $index->permissions());
    }

    /**
     * @test
     */
    function it_builds_a_permissions_index_for_a_deep_nested_group_layout()
    {
        $layout = new LayoutData([
            'layout' => $this->getComplexLayoutArray(),
        ]);

        $filter = new MenuPermissionsFilter();
        $index = $filter->buildPermissionsIndex($layout);

        static::assertInstanceOf(MenuPermissionsIndexDataInterface::class, $index);
        static::assertEquals([
            base64_encode('group-a') .'.' . base64_encode('group-b') => ['permission-a', 'permission-b'],
            base64_encode('group-a') .'.' . base64_encode('group-c') => [],
        ], $index->index());
        static::assertEquals([
            'permission-a',
            'permission-b',
            'permission-z',
            'permission-x',
            'permission-y',
        ], $index->permissions());
    }


    // ------------------------------------------------------------------------------
    //      Filtering
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_filters_a_layout_based_on_a_nested_filter_index()
    {
        $user = $this->getMockUser();

        $user->method('can')
            ->willReturnCallback(function ($permission) {
                switch ($permission) {
                    case 'permission-a':
                    case 'permission-z':
                    case 'permission-y':
                        return true;

                    default:
                        return false;
                }
            });

        $filter = new MenuPermissionsFilter();
        $index = new PermissionsIndexData([
            'layout' => [
                base64_encode('group-a') .'.' . base64_encode('group-b') => ['permission-a', 'permission-b'],
                base64_encode('group-a') .'.' . base64_encode('group-c') => [],
            ],
            'permissions' => [
                'permission-a',
                'permission-b',
                'permission-z',
                'permission-x',
                'permission-y',
            ],
        ]);

        $layout = new LayoutData([
            'layout' => $this->getComplexLayoutArray(),
        ]);

        $layout = $filter->filterLayout($layout, $user, $index);

        static::assertInstanceOf(MenuLayoutDataInterface::class, $layout);
        $array = $layout->layout();
        static::assertCount(1, $array, 'Topmost layer should have 1 entry');
        static::assertArrayHasKey('group-a', $array, 'Topmost layer should have "group-a" key');

        static::assertCount(5, $array['group-a']->children(), 'Group-a layer should have 3 entries');
        static::assertArraySubset(
            [1, 2, 'group-b','group-c', 'group-d'], // 0 is filtered out
            array_keys($array['group-a']->children()),
            'Group-a layer should have keys: 1, 2, group-b, group-c, group-d'
        );

        // Group b
        static::assertCount(
            1,
            $array['group-a']->children()['group-b']->children(),
            'Group-b layer should have 1 entry'
        );
        static::assertEquals(
            'test-d',
            head($array['group-a']->children()['group-b']->children())['id'],
            'Group-b layer should have 1 entry'
        );

        // Group d
        static::assertCount(
            2,
            $array['group-a']->children()['group-d']->children(),
            'Group-d layer should have 2 entries'
        );
        static::assertEquals(
            'test-g',
            $array['group-a']->children()['group-d']->children()[0]['id'],
            'Group-d layer should have "test-g"'
        );
        static::assertEquals(
            'test-h',
            $array['group-a']->children()['group-d']->children()[1]['id'],
            'Group-d layer should have "test-h"'
        );
    }

    /**
     * @test
     */
    function it_removes_empty_groups_when_filtering()
    {
        $user = $this->getMockUser();

        $user->method('can')
            ->willReturnCallback(function ($permission) {
                switch ($permission) {
                    case 'permission-z':
                    case 'permission-y':
                        return true;

                    default:
                        return false;
                }
            });

        $filter = new MenuPermissionsFilter();
        $index = new PermissionsIndexData([
            'layout' => [
                base64_encode('group-a') .'.' . base64_encode('group-b') => ['permission-a', 'permission-b'],
                base64_encode('group-a') .'.' . base64_encode('group-c') => [],
            ],
            'permissions' => [
                'permission-a',
                'permission-b',
                'permission-z',
                'permission-x',
                'permission-y',
            ],
        ]);

        $layout = new LayoutData([
            'layout' => $this->getComplexLayoutArray(),
        ]);

        $layout = $filter->filterLayout($layout, $user, $index);

        static::assertInstanceOf(MenuLayoutDataInterface::class, $layout);
        $array = $layout->layout();
        static::assertCount(1, $array, 'Topmost layer should have 1 entry');
        static::assertArrayHasKey('group-a', $array, 'Topmost layer should have "group-a" key');

        static::assertCount(4, $array['group-a']->children(), 'Group-a layer should have 3 entries');
        static::assertArrayNotHasKey(
            'group-b',
            $array['group-a']->children(),
            'Group-a should not have empty group "group-b"'
        );
    }


    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|UserInterface
     */
    protected function getMockUser()
    {
        return $this->getMockBuilder(UserInterface::class)->getMock();
    }

    /**
     * @return array
     */
    protected function getComplexLayoutArray()
    {
        return [
            // Group with mixed presences (should not be indexed itself)
            'group-a' => new MenuPresence([
                'id'    => 'group-a',
                'type'  => MenuPresenceType::GROUP,
                'label' => 'something',
                'children' => [
                    new MenuPresence([
                        'id'          => 'test-a',
                        'type'        => MenuPresenceType::ACTION,
                        'label'       => 'something',
                        'permissions' => 'permission-x',
                    ]),
                    new MenuPresence([
                        'id'    => 'test-c',
                        'type'  => MenuPresenceType::ACTION,
                        'label' => 'something',
                    ]),
                    new MenuPresence([
                        'id'          => 'test-b',
                        'type'        => MenuPresenceType::ACTION,
                        'label'       => 'something',
                        'permissions' => 'permission-y',
                    ]),
                    // Group with no unconditional (action) presences (should be indexed)
                    'group-b' => new MenuPresence([
                        'id'    => 'group-b',
                        'type'  => MenuPresenceType::GROUP,
                        'label' => 'something',
                        'children' => [
                            new MenuPresence([
                                'id'          => 'test-d',
                                'type'        => MenuPresenceType::ACTION,
                                'label'       => 'something',
                                'permissions' => 'permission-a',
                            ]),
                            new MenuPresence([
                                'id'          => 'test-e',
                                'type'        => MenuPresenceType::ACTION,
                                'label'       => 'something',
                                'permissions' => 'permission-b',
                            ]),
                        ]
                    ]),
                    // Group with unconditional (action) presences (should be indexed)
                    'group-c' => new MenuPresence([
                        'id'    => 'group-c',
                        'type'  => MenuPresenceType::GROUP,
                        'label' => 'something',
                        'children' => [
                            new MenuPresence([
                                'id'    => 'test-f',
                                'type'  => MenuPresenceType::ACTION,
                                'label' => 'something',
                            ]),
                        ]
                    ]),
                    // Group with mixed presences (should not be indexed)
                    'group-d' => new MenuPresence([
                        'id'    => 'group-d',
                        'type'  => MenuPresenceType::GROUP,
                        'label' => 'something',
                        'children' => [
                            new MenuPresence([
                                'id'    => 'test-g',
                                'type'  => MenuPresenceType::ACTION,
                                'label' => 'something',
                            ]),
                            new MenuPresence([
                                'id'          => 'test-h',
                                'type'        => MenuPresenceType::ACTION,
                                'label'       => 'something',
                                'permissions' => 'permission-z',
                            ]),
                        ]
                    ]),
                ]
            ]),
        ];
    }

}
