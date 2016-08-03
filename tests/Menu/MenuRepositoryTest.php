<?php
namespace Czim\CmsCore\Test\Menu;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ManagerInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Menu\MenuRepository;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Support\Collection;

class MenuRepositoryTest extends TestCase
{

    /**
     * Testing menu groups config.
     *
     * @var array
     */
    protected $menuGroupsConfig = [];

    /**
     * Testing menu modules config.
     *
     * @var array
     */
    protected $menuModulesConfig = [];

    /**
     * @var Collection
     */
    protected $modules;


    /**
     * @test
     */
    function it_initializes_succesfully()
    {
        $menu = new MenuRepository($this->getMockCore(), $this->getMockAuth());

        $menu->initialize();
    }

    /**
     * @test
     */
    function it_retrieves_an_ungrouped_menu_presence_defined_by_module_as_instance()
    {
        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped   = $menu->getMenuGroups();
        $ungrouped = $menu->getMenuUngrouped();

        $this->assertInstanceOf(Collection::class, $ungrouped);
        $this->assertCount(1, $ungrouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->first());
        $this->assertEquals('test-a', $ungrouped->first()->id());

        $this->assertInstanceOf(Collection::class, $grouped);
        $this->assertCount(0, $grouped);
    }

    /**
     * @test
     */
    function it_retrieves_an_ungrouped_menu_presence_defined_by_module_as_array()
    {
        $this->modules = collect([
            'test-b' => $this->getMockModuleWithPresenceArray(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $ungrouped = $menu->getMenuUngrouped();

        $this->assertInstanceOf(Collection::class, $ungrouped);
        $this->assertCount(1, $ungrouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->first());
    }

    /**
     * @test
     */
    function it_groups_menu_presence_according_to_configured_group_structure()
    {
        $this->menuGroupsConfig = $this->getTestGroupConfig();

        $this->menuModulesConfig = [
            'test-a' => [
                'group' => 'nested-group',
            ],
        ];

        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped   = $menu->getMenuGroups();
        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(0, $ungrouped);

        $this->assertCount(1, $grouped);
        $this->assertEquals('top-level-group', $grouped->first()->id());
        $this->assertCount(1, $grouped->first()->children(), "Top level group should have 1 child");

        $childOfTopGroup = head($grouped->first()->children());

        $this->assertEquals('nested-group', $childOfTopGroup->id(), "Child of top level group should be nested group");
        $this->assertCount(1, $childOfTopGroup->children(), "Nested group should have 1 child");

        $childOfNestedGroup = head($childOfTopGroup->children());

        $this->assertEquals('test-a', $childOfNestedGroup->id(), "Child of nested group should be the assigned presence");
    }

    /**
     * @test
     */
    function it_groups_menu_presence_according_to_configured_group_structure_as_string_only_config_value()
    {
        $this->menuGroupsConfig = $this->getTestGroupConfig();

        $this->menuModulesConfig = [
            'test-a' => 'nested-group',
        ];

        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped   = $menu->getMenuGroups();
        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(0, $ungrouped);

        $this->assertCount(1, $grouped);
        $this->assertEquals('top-level-group', $grouped->first()->id());
        $this->assertCount(1, $grouped->first()->children(), "Top level group should have 1 child");

        $childOfTopGroup = head($grouped->first()->children());

        $this->assertEquals('nested-group', $childOfTopGroup->id(), "Child of top level group should be nested group");
        $this->assertCount(1, $childOfTopGroup->children(), "Nested group should have 1 child");

        $childOfNestedGroup = head($childOfTopGroup->children());

        $this->assertEquals('test-a', $childOfNestedGroup->id(), "Child of nested group should be the assigned presence");
    }

    /**
     * @test
     */
    function it_leaves_presences_ungrouped_without_error_if_a_configured_group_key_is_unknown()
    {
        $this->menuGroupsConfig = $this->getTestGroupConfig();

        $this->menuModulesConfig = [
            'test-a' => [
                'group' => 'non-existent-group',
            ],
        ];

        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped   = $menu->getMenuGroups();
        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(0, $grouped);
        $this->assertCount(1, $ungrouped);
    }

    /**
     * @test
     */
    function it_does_not_retrieve_empty_groups()
    {
        $this->menuGroupsConfig = $this->getTestGroupConfig();

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped = $menu->getMenuGroups();

        $this->assertCount(0, $grouped);
    }

    /**
     * @test
     */
    function it_normalizes_an_array_of_presences_for_a_module()
    {
        $this->modules = collect([
            'test-e' => $this->getMockModuleWitPresencesInArray(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(2, $ungrouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->first());
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->last());
    }
    
    /**
     * @test
     */
    function it_normalizes_nested_presences()
    {
        $this->modules = collect([
            'test-f' => $this->getMockModuleWithNestedPresences(),
        ]);

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(1, $ungrouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->first());

        $children = $ungrouped->first()->children();

        $this->assertCount(2, $children);
        $this->assertInstanceOf(MenuPresenceInterface::class, $children[0]);
        $this->assertInstanceOf(MenuPresenceInterface::class, $children[1]);
    }

    /**
     * @test
     */
    function it_includes_presences_with_permissions_that_the_logged_in_user_has()
    {
        $this->modules = collect([
            'test-c' => $this->getMockModuleWithPermissions(),
        ]);

        $auth = $this->getMockAuth();

        $auth->method('can')
             ->with(['can.do.something', 'can.do.more'])
             ->willReturn(true);

        $menu = new MenuRepository($this->getMockCore(), $auth);
        $menu->initialize();

        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(1, $ungrouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->first());
    }

    /**
     * @test
     */
    function it_excludes_presences_that_are_not_permitted_for_the_logged_in_user()
    {
        $this->modules = collect([
            'test-c' => $this->getMockModuleWithPermissions(),
        ]);

        $auth = $this->getMockAuth();

        $auth->method('can')
             ->willReturn(false);

        $menu = new MenuRepository($this->getMockCore(), $auth);
        $menu->initialize();

        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(0, $ungrouped);
    }

    /**
     * @test
     */
    function it_allows_disabling_of_module_presences_by_setting_presence_to_false_in_module_configuration()
    {
        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(false),
        ]);

        $this->menuModulesConfig = [
            'test-a' => [
                // overrule presence to disable it
                'presence' => false,
            ],
        ];

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped   = $menu->getMenuGroups();
        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(0, $ungrouped);
        $this->assertCount(0, $grouped);
    }

    /**
     * @test
     */
    function it_allows_overriding_of_module_presence_data_by_setting_values_in_module_configuration()
    {
        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(false),
        ]);

        $this->menuModulesConfig = [
            'test-a' => [
                // overrule presence to disable it
                'presence' => [
                    'id'     => 'test-overruled',
                    'type'   => MenuPresenceType::ACTION,
                    'label'  => 'something',
                    'action' => 'test',
                ],
            ],
        ];

        $menu = $this->makeMenuRepository();
        $menu->initialize();

        $grouped   = $menu->getMenuGroups();
        $ungrouped = $menu->getMenuUngrouped();

        $this->assertCount(1, $ungrouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $ungrouped->first());
        $this->assertEquals('test-overruled', $ungrouped->first()->id(), "Presence should be overruled");

        $this->assertCount(0, $grouped);
    }

    
    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCore()
    {
        $mock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $mock->method('modules')
             ->willReturn($this->getMockModuleManager($this->modules));

        $mock->method('moduleConfig')
             ->willReturnCallback(function ($key) {
                 switch ($key) {
                     case 'menu.groups':
                         return $this->menuGroupsConfig;

                     case 'menu.modules':
                         return $this->menuModulesConfig;
                 }

                 return null;
             });

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticatorInterface
     */
    protected function getMockAuth()
    {
        $mock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();

        return $mock;
    }

    /**
     * @param null|Collection $modules
     * @return ManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockModuleManager($modules = null)
    {
        $mock = $this->getMockBuilder(ManagerInterface::class)->getMock();

        $modules = $modules ?: new Collection;

        $mock->expects($this->once())
             ->method('getModules')
             ->willReturn($modules);

        return $mock;
    }

    /**
     * @return Collection
     */
    protected function getMockModules()
    {
        return new Collection([
            'test-a' => $this->getMockModuleWithPresenceInstance(),
        ]);
    }

    /**
     * @param bool $expected    whether the menu presence call is expected
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithPresenceInstance($expected = true)
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->method('getKey')->willReturn('test-a');

        $mock->expects($expected ? $this->once() : $this->any())
             ->method('getMenuPresence')
             ->willReturn(
                 new MenuPresence([
                     'id'    => 'test-a',
                     'type'  => MenuPresenceType::ACTION,
                     'label' => 'something',
                 ])
             );

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithPresenceArray()
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->expects($this->once())
            ->method('getMenuPresence')
            ->willReturn([
                'id'     => 'test-b',
                'type'   => MenuPresenceType::ACTION,
                'label'  => 'something',
                'action' => 'test',
            ]);

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithPermissions()
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->expects($this->once())
            ->method('getMenuPresence')
            ->willReturn([
                'id'          => 'test-c',
                'type'        => MenuPresenceType::ACTION,
                'label'       => 'something',
                'action'      => 'test',
                'permissions' => ['can.do.something', 'can.do.more'],
            ]);

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWitPresencesInArray()
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->expects($this->once())
            ->method('getMenuPresence')
            ->willReturn([
                [
                    'id'     => 'test-p',
                    'type'   => MenuPresenceType::ACTION,
                    'label'  => 'something',
                    'action' => 'test',
                ],
                [
                    'id'     => 'test-q',
                    'type'   => MenuPresenceType::ACTION,
                    'label'  => 'something',
                    'action' => 'test',
                ],
            ]);

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithNestedPresences()
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->expects($this->once())
            ->method('getMenuPresence')
            ->willReturn([
                'id'       => 'test-d',
                'type'     => MenuPresenceType::GROUP,
                'label'    => 'some group',
                'children' => [
                    [
                        'id'     => 'test-x',
                        'type'   => MenuPresenceType::ACTION,
                        'label'  => 'something',
                        'action' => 'test',
                    ],
                    [
                        'id'     => 'test-y',
                        'type'   => MenuPresenceType::ACTION,
                        'label'  => 'something',
                        'action' => 'test',
                    ],
                ]
            ]);

        return $mock;
    }

    /**
     * @return MenuRepository
     */
    protected function makeMenuRepository()
    {
        return new MenuRepository($this->getMockCore(), $this->getMockAuth());
    }

    /**
     * @return array
     */
    protected function getTestGroupConfig()
    {
        return [
            'top-level-group' => [
                'label' => 'Top level group',
                'children' => [
                    'nested-group' => [
                        'label' => 'Nested group',
                    ],
                ],
            ],
        ];
    }

}
