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

        $this->assertInstanceOf(Collection::class, $grouped);
        $this->assertCount(1, $grouped);
        $this->assertInstanceOf(MenuPresenceInterface::class, $grouped->first());

        $this->assertInstanceOf(Collection::class, $ungrouped);
        $this->assertCount(0, $ungrouped);
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
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithPresenceInstance()
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->method('getKey')->willReturn('test-a');

        $mock->expects($this->once())
             ->method('getMenuPresence')
             ->willReturn(
                 new MenuPresence([
                     'id'   => 'test-a',
                     'type' => MenuPresenceType::ACTION,
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
                'id'   => 'test-b',
                'type' => MenuPresenceType::ACTION,
                'label' => 'something',
                'action' => 'test',
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
