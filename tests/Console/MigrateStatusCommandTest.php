<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Console;

use Czim\CmsCore\Test\SimpleDbTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateStatusCommandTest extends SimpleDbTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    /**
     * @test
     */
    function it_reports_no_migrations_if_the_migration_table_is_not_installed()
    {
        static::assertFalse(
            Schema::connection('testbench')->hasTable('cms_migrations'),
            'CMS migration table should not exist yet'
        );

        static::assertEquals(0, $this->artisan('cms:migrate:status'));

        static::assertRegExp('#no (cms\s+)?migrations#i', $this->getArtisanOutput());
    }

    /**
     * @test
     */
    function it_reports_no_migrations_if_migration_table_is_empty()
    {
        $this->createMigrationTable();

        static::assertEquals(0, $this->artisan('cms:migrate:status'));

        static::assertRegExp('#no (cms\s+)?migrations#i', $this->getArtisanOutput());
    }

    /**
     * @test
     */
    function it_reports_migration_status()
    {
        $this->setHelperCmsMigrationPath();
        $this->createMigrationTable();

        // Status when no migrations are run
        static::assertEquals(0, $this->artisan('cms:migrate:status'));

        $output = $this->getArtisanOutput();

        static::assertRegExp('#\|\s+n\s+\|\s+2017_01_01_100000_create_test_records_table#is', $output);
        static::assertRegExp('#\|\s+n\s+\|\s+2017_01_01_200000_create_more_test_records_table#is', $output);

        // Status when a single migration has run
        DB::table('cms_migrations')->insert([
            'migration' => '2017_01_01_100000_create_test_records_table',
            'batch'     => 1,
        ]);

        static::assertEquals(0, $this->artisan('cms:migrate:status'));

        $output = $this->getArtisanOutput();

        static::assertRegExp('#\|\s+y\s+\|\s+2017_01_01_100000_create_test_records_table#is', $output);
        static::assertRegExp('#\|\s+n\s+\|\s+2017_01_01_200000_create_more_test_records_table#is', $output);
    }

}
