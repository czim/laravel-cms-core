<?php
namespace Czim\CmsCore\Console;

use Czim\CmsCore\Test\SimpleDbTestCase;
use Illuminate\Support\Facades\Schema;

class MigrateInstallCommandTest extends SimpleDbTestCase
{

    /**
     * @test
     */
    function it_installs_the_migration_table_if_it_does_not_exist()
    {
        static::assertFalse(
            Schema::connection('testbench')->hasTable('cms_migrations'), '
            CMS migration table should not exist yet'
        );

        $this->artisan('cms:migrate:install')->assertExitCode(0);

        static::assertTrue(
            Schema::connection('testbench')->hasTable('cms_migrations'),
            'CMS migration table was not created'
        );
    }

}
