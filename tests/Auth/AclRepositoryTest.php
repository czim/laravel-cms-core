<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Auth;

use Czim\CmsCore\Auth\AclRepository;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Support\Data\AclPresence;
use Czim\CmsCore\Support\Enums\AclPresenceType;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;
use Mockery;
use UnexpectedValueException;

class AclRepositoryTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_initializes_succesfully()
    {
        $acl = new AclRepository($this->getMockCore());

        $acl->initialize();
    }

    /**
     * @test
     */
    function it_only_initializes_once()
    {
        $acl = new AclRepository($this->getMockCore(null, true));

        $acl->initialize();
        $acl->initialize();
    }

    /**
     * @test
     */
    function it_ignores_acl_presences_defined_by_module_as_null_or_false()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-a' => $this->getMockModuleWithCustomPresence(null),
                'test-b' => $this->getMockModuleWithCustomPresence(false),
            ])
        ));

        $presences = $acl->getAclPresences();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertCount(0, $presences);
    }

    /**
     * @test
     */
    function it_retrieves_acl_presence_defined_by_module_as_instance()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-a' => $this->getMockModuleWithPresenceInstance(),
            ])
        ));

        $presences = $acl->getAclPresences();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertCount(1, $presences);
        static::assertInstanceOf(AclPresenceInterface::class, $presences->first());
    }

    /**
     * @test
     */
    function it_retrieves_acl_presence_defined_by_module_as_array()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-b' => $this->getMockModuleWithPresenceArray(),
            ])
        ));

        $presences = $acl->getAclPresences();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertCount(1, $presences);
        static::assertInstanceOf(AclPresenceInterface::class, $presences->first(), 'Array was not normalized to presence instance');
    }

    /**
     * @test
     */
    function it_retrieves_acl_presences_defined_by_module_as_an_array_of_arrays()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-c' => $this->getMockModuleWithMultiplePresencesInArray(),
            ])
        ));

        $presences = $acl->getAclPresences();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertCount(2, $presences);
        static::assertInstanceOf(AclPresenceInterface::class, $presences->first());
        static::assertInstanceOf(AclPresenceInterface::class, $presences->last());
    }

    /**
     * @test
     */
    function it_retrieves_combined_acl_presences_defined_by_modules()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-a' => $this->getMockModuleWithPresenceInstance(),
                'test-b' => $this->getMockModuleWithPresenceArray(),
                'test-c' => $this->getMockModuleWithCustomPresence([
                    'id'          => 'test-c',
                    'permissions' => []
                ]),
            ])
        ));

        $presences = $acl->getAclPresences();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertCount(3, $presences);
        static::assertInstanceOf(AclPresenceInterface::class, $presences->first());
        static::assertInstanceOf(AclPresenceInterface::class, $presences->values()[1]);
        static::assertInstanceOf(AclPresenceInterface::class, $presences->values()[2]);
        static::assertEquals('test-a', $presences->first()->id());
        static::assertEquals('test-b', $presences->values()[1]->id);
        static::assertEquals('test-c', $presences->values()[2]->id);
    }

    /**
     * @test
     */
    function it_retrieves_acl_presences_defined_by_a_single_module()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-a' => $this->getMockModuleWithPresenceInstance(),
                'test-b' => $this->getMockModuleWithPresenceArray(),
            ])
        ));

        $presences = $acl->getAclPresencesByModule('test-b');

        static::assertInstanceOf(Collection::class, $presences);
        static::assertCount(1, $presences);
        static::assertInstanceOf(AclPresenceInterface::class, $presences->first());
        static::assertEquals('test-b', $presences->first()->id());
    }

    /**
     * @test
     */
    function it_retrieves_collapsed_permissions_for_all_modules()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-a' => $this->getMockModuleWithPresenceInstance(),
                'test-b' => $this->getMockModuleWithPresenceArray(),
            ])
        ));

        $permissions = $acl->getAllPermissions();

        static::assertIsArray($permissions);
        static::assertCount(5, $permissions, 'Combined permissions should be complete without duplicates');
    }

    /**
     * @test
     */
    function it_retrieves_collapsed_permissions_for_a_single_module()
    {
        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test-a' => $this->getMockModuleWithPresenceInstance(),
                'test-b' => $this->getMockModuleWithPresenceArray(),
            ])
        ));

        $permissions = $acl->getModulePermissions('test-a');

        static::assertIsArray($permissions);
        static::assertCount(3, $permissions);
    }

    /**
     * @test
     */
    function it_returns_an_empty_array_if_no_permissions_were_defined()
    {
        $acl = new AclRepository($this->getMockCore());

        $permissions = $acl->getAllPermissions();

        static::assertIsArray($permissions);
        static::assertCount(0, $permissions);
    }

    /**
     * @test
     */
    function it_returns_an_empty_array_if_an_unloaded_module_key_is_given()
    {
        $acl = new AclRepository($this->getMockCore());

        $permissions = $acl->getModulePermissions('does-not-exist');

        static::assertIsArray($permissions);
        static::assertCount(0, $permissions);
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_invalid_nested_presence_data_is_set()
    {
        $this->expectException(UnexpectedValueException::class);

        $acl = new AclRepository($this->getMockCore(
            new Collection([
                'test' => $this->getMockModuleWithCustomPresence([ false ]),
            ])
        ));

        $acl->getAclPresences();
    }

    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @param null|Collection $modules
     * @param bool            $expect  whether to force strict expectactions
     * @return CoreInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockCore($modules = null, $expect = false)
    {
        /** @var CoreInterface|Mockery\Mock|Mockery\MockInterface $mock */
        $mock = Mockery::mock(CoreInterface::class);

        $mock = $mock->shouldReceive('modules');

        if ($expect) {
            $mock = $mock->once();
        }

        return $mock->andReturn($this->getMockModuleManager($modules))->getMock();
    }

    /**
     * @param null|Collection $modules
     * @return ModuleManagerInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockModuleManager(?Collection $modules = null)
    {
        /** @var ModuleManagerInterface|Mockery\Mock|Mockery\MockInterface $mock */
        $mock = Mockery::mock(ModuleManagerInterface::class);

        $modules = $modules ?: new Collection;

        $mock->shouldReceive('getModules')->once()->andReturn($modules);

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
     * @return ModuleInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockModuleWithPresenceInstance()
    {
        /** @var ModuleInterface|Mockery\Mock|Mockery\MockInterface $mock */
        $mock = Mockery::mock(ModuleInterface::class);

        $mock->shouldReceive('getAclPresence')->once()
             ->andReturn(
                 new AclPresence([
                     'id'   => 'test-a',
                     'type' => AclPresenceType::GROUP,
                     'label' => 'something',
                     'permissions' => [
                         'test.permission.show',
                         'test.permission.edit',
                         'test.permission.create',
                     ],
                 ])
             );

        return $mock;
    }

    /**
     * @return ModuleInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockModuleWithPresenceArray()
    {
        /** @var ModuleInterface|Mockery\Mock|Mockery\MockInterface $mock */
        $mock = Mockery::mock(ModuleInterface::class);

        $mock->shouldReceive('getAclPresence')->once()
            ->andReturn([
                'id'   => 'test-b',
                'type' => AclPresenceType::GROUP,
                'label' => 'something',
                'permissions' => [
                    'test.permission.show',
                    'test.permission.delete',
                    'test.other-permission.show',
                ],
            ]);

        return $mock;
    }

    /**
     * @return ModuleInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockModuleWithMultiplePresencesInArray()
    {
        /** @var ModuleInterface|Mockery\Mock|Mockery\MockInterface $mock */
        $mock = Mockery::mock(ModuleInterface::class);

        $mock->shouldReceive('getAclPresence')->once()
            ->andReturn([
                [
                    'id'   => 'test-b',
                    'type' => AclPresenceType::GROUP,
                    'label' => 'something',
                    'permissions' => [
                        'test.permission.show',
                        'test.permission.delete',
                        'test.other-permission.show',
                    ],
                ],
                [
                    'id'   => 'test-c',
                    'type' => AclPresenceType::GROUP,
                    'label' => 'something-more',
                    'permissions' => [
                        'test.permission.create',
                    ],
                ],
            ]);

        return $mock;
    }

    /**
     * @param mixed $presence
     * @return ModuleInterface|Mockery\Mock|Mockery\MockInterface
     */
    protected function getMockModuleWithCustomPresence($presence)
    {
        /** @var ModuleInterface|Mockery\Mock|Mockery\MockInterface $mock */
        $mock = Mockery::mock(ModuleInterface::class);

        $mock->shouldReceive('getAclPresence')->once()->andReturn($presence);

        return $mock;
    }

    protected function makeAclRepository(): AclRepository
    {
        return new AclRepository($this->getMockCore());
    }

}
