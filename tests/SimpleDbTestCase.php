<?php
namespace Czim\CmsCore\Test;

use Illuminate\Support\Facades\Schema;

abstract class SimpleDbTestCase extends CmsBootTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    /**
     * Creates and verifies the cms migrations table.
     */
    protected function createMigrationTable()
    {
        $result = $this->artisan('cms:migrate:install');

        if ($result instanceof \Illuminate\Foundation\Testing\PendingCommand) {
            $result->assertExitCode(0);
        } else {
            static::assertEquals(0, $result);
        }
    }

    /**
     * Sets the migrations cms path to a directory with test migrations.
     */
    protected function setHelperCmsMigrationPath()
    {
        $this->app['config']->set(
            'cms-core.database.migrations.path',
            '../../../../../tests/Helpers/migrations/cms'
        );
    }

}
