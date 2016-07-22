<?php
namespace Czim\CmsCore\Support\Database;

use Illuminate\Database\Migrations\Migration;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;

class CmsMigration extends Migration
{

    /**
     * Get the migration connection name.
     *
     * @todo This appears not to be used by the Migrator? Weird. Remove or keep?
     *
     * @return string
     */
    public function getConnection()
    {
        $connection = parent::getConnection();

        if ( ! app()->bound(Component::CORE)) {
            return $connection;
        }

        /** @var CoreInterface $core */
        $core = app(Component::CORE);
        $cmsConnection = $core->config('database.driver');

        // If no separate database driver is configured for the CMS,
        // we can use the normal driver and rely on the prefix alone.
        if ( ! $cmsConnection) {
            return $connection;
        }

        // If we're running tests, make sure we use the correct override
        // for the CMS database connection/driver.
        // N.B. This is ignored on purpose if the default connection is not overridden normally.
        if (app()->runningUnitTests()) {
            $cmsConnection = $core->config('database.testing.driver') ?: $cmsConnection;
        }

        return $cmsConnection;
    }

    /**
     * @param string $name
     * @return string
     */
    protected function prefixCmsTable($name)
    {
        return config('cms-core.database.prefix', '') . $name;
    }

}
