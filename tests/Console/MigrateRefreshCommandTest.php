<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Console;

use CreateTestRecordsTable;
use Czim\CmsCore\Test\SimpleDbTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateRefreshCommandTest extends SimpleDbTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $this->setHelperCmsMigrationPath();
    }

    /**
     * @test
     */
    function it_refreshes_database_migrations()
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
        $this->artisan('cms:migrate:refresh', ['--seed' => true])->assertExitCode(0);

        static::assertTrue(Schema::connection('testbench')->hasTable('cms_test_records'));
        static::assertTrue(Schema::connection('testbench')->hasTable('cms_more_test_records'));

        $this->assertDatabaseHas('cms_test_records', [ 'description' => 'testing!' ]);
    }

}
