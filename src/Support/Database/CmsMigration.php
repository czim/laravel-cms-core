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
     * @return string|null
     */
    public function getConnection(): ?string
    {
        $connection = parent::getConnection();

        if ( ! app()->bound(Component::CORE)) {
            // @codeCoverageIgnoreStart
            return $connection;
            // @codeCoverageIgnoreEnd
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
    protected function prefixCmsTable(?string $name): string
    {
        return config('cms-core.database.prefix', '') . $name;
    }

}
