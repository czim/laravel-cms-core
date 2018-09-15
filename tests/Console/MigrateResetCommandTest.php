<?php
namespace Czim\CmsCore\Console;

use CreateTestRecordsTable;
use Czim\CmsCore\Test\SimpleDbTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateResetCommandTest extends SimpleDbTestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->setHelperCmsMigrationPath();
        $this->withoutMockingConsoleOutput();
    }

    /**
     * @test
     */
    function it_resets_database_migrations()
    {
        // Set up
        $this->setHelperCmsMigrationPath();
        $this->createMigrationTable();

        (new CreateTestRecordsTable())->up();

        DB::table('cms_migrations')->insert([
            'migration' => '2017_01_01_100000_create_test_records_table',
            'batch'     => 1,
        ]);

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_records'), 'Failed to fake migration');

        // Test
        static::assertEquals(0, $this->artisan('cms:migrate:reset'));

        static::assertFalse(Schema::connection('testbench')->hasTable('cms_test_records'));
        static::assertFalse(Schema::connection('testbench')->hasTable('cms_more_test_records'));
    }

    /**
     * @test
     */
    function it_reports_when_the_migration_table_does_not_exist()
    {
        static::assertEquals(0, $this->artisan('cms:migrate:reset'));

        static::assertRegExp('#not found#i', $this->getArtisanOutput());
    }

}
