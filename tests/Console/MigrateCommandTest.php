<?php
namespace Czim\CmsCore\Console;

use Czim\CmsCore\Test\SimpleDbTestCase;
use Illuminate\Support\Facades\Schema;

class MigrateCommandTest extends SimpleDbTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->setHelperCmsMigrationPath();
    }

    /**
     * @test
     */
    function it_runs_migrations_from_the_configured_directory()
    {
        $this->createMigrationTable();

        static::assertEquals(0, $this->artisan('cms:migrate'));

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_records'));
        static::assertTrue(Schema::connection('testbench')->hasTable('cms_more_test_records'));
    }

    /**
     * @test
     */
    function it_installs_the_migrations_table_if_it_does_not_exist()
    {
        static::assertEquals(0, $this->artisan('cms:migrate'));

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_migrations'));
        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_records'));
        static::assertTrue(Schema::connection('testbench')->hasTable('cms_more_test_records'));
    }

    /**
     * @test
     */
    function it_allows_overriding_the_database_connection_with_a_command_option()
    {
        $this->app['config']->set('database.connections.alternative', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        static::assertEquals(0, $this->artisan('cms:migrate', [
            '--database' => 'alternative',
        ]));

        static::assertFalse(Schema::connection('testbench')->hasTable('cms_migrations'));
        static::assertTrue(Schema::connection('alternative')->hasTable('cms_migrations'));
        static::assertTrue(Schema::connection('alternative')->hasTable('cms_test_records'));
        static::assertTrue(Schema::connection('alternative')->hasTable('cms_more_test_records'));
    }

    /**
     * @test
     */
    function it_allows_overriding_the_path_with_a_command_option()
    {
        $this->createMigrationTable();

        static::assertEquals(0, $this->artisan('cms:migrate', [
            '--path' => '../../../../tests/Helpers/migrations/alt',
        ]));

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_alternative_records'));
    }

    /**
     * @test
     */
    function it_uses_a_configured_driver_for_testing_if_a_default_driver_is_configured()
    {
        $this->app['config']->set('database.connections.cms_testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $this->app['config']->set('cms-core.database.driver', 'alternative');

        static::assertEquals(0, $this->artisan('cms:migrate'));

        static::assertTrue(Schema::connection('cms_testing')->hasTable('cms_migrations'));
        static::assertTrue(Schema::connection('cms_testing')->hasTable('cms_test_records'));
        static::assertTrue(Schema::connection('cms_testing')->hasTable('cms_more_test_records'));
    }

}
