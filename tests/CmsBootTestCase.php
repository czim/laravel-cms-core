<?php
namespace Czim\CmsCore\Test;

use Czim\CmsCore\Providers\CmsCoreServiceProvider;
use Czim\CmsCore\Support\Enums\Component;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Foundation\Application;

abstract class CmsBootTestCase extends TestCase
{

    /**
     * {@inheritdoc}
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->deleteMenuCacheFile();

        /** @var Repository $config */
        $config = $app['config'];

        // Load the CMS even when unit testing
        $config->set('cms-core.testing', true);
        $config->set('cms-modules.modules', []);

        // Set up service providers for tests, excluding what is not part of this package
        $config->set('cms-core.providers', [
            \Czim\CmsCore\Providers\ModuleManagerServiceProvider::class,
            \Czim\CmsCore\Providers\LogServiceProvider::class,
            \Czim\CmsCore\Providers\MiddlewareServiceProvider::class,
            \Czim\CmsCore\Providers\MigrationServiceProvider::class,
            \Czim\CmsCore\Providers\ViewServiceProvider::class,
            //\Czim\CmsAuth\Providers\CmsAuthServiceProvider::class,
            //\Czim\CmsTheme\Providers\CmsThemeServiceProvider::class,
            //\Czim\CmsAuth\Providers\Api\OAuthSetupServiceProvider::class,
            \Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider::class,
            \Czim\CmsCore\Providers\RouteServiceProvider::class,
            \Czim\CmsCore\Providers\Api\ApiRouteServiceProvider::class,
        ]);

        $config->set('cms-api.providers', [
            //\Czim\CmsAuth\Providers\Api\FluentStorageServiceProvider::class,
            //\Czim\CmsAuth\Providers\Api\OAuth2ServerServiceProvider::class,
        ]);

        // Mock component bindings in the config
        $config->set(
            'cms-core.bindings', [
                Component::BOOTCHECKER => $this->getTestBootCheckerBinding(),
                Component::CACHE       => \Czim\CmsCore\Core\Cache::class,
                Component::CORE        => \Czim\CmsCore\Core\Core::class,
                Component::MODULES     => \Czim\CmsCore\Modules\ModuleManager::class,
                Component::API         => \Czim\CmsCore\Api\ApiCore::class,
                Component::ACL         => \Czim\CmsCore\Auth\AclRepository::class,
                Component::MENU        => \Czim\CmsCore\Menu\MenuRepository::class,
                Component::AUTH        => 'mock-cms-auth',
        ]);

        $this->mockBoundCoreExternalComponents($app);

        $app->register(CmsCoreServiceProvider::class);
    }

    protected function getMenuCachePath(): string
    {
        return realpath(__DIR__ .'/../vendor/orchestra/testbench-core/laravel/bootstrap/cache') . '/cms_menu.php';
    }

    /**
     * Deletes the menu cache file if it exists.
     */
    protected function deleteMenuCacheFile(): void
    {
        if (file_exists($this->getMenuCachePath())) {
            unlink($this->getMenuCachePath());
        }
    }

}
