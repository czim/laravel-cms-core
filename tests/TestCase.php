<?php
namespace Czim\CmsCore\Test;

use Illuminate\Contracts\Foundation\Application;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Providers\CmsCoreServiceProvider;
use Czim\CmsCore\Support\Enums\Component;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // todo remove after fixing package config
        $app['config']->set('cms-modules.modules', []);

        // Load the CMS even when unit testing
        $app['config']->set('cms-core.testing', true);

        // Set up service providers for tests, excluding what is not part of this package
        $app['config']->set('cms-core.providers', [
            \Czim\CmsCore\Providers\LogServiceProvider::class,
            \Czim\CmsCore\Providers\RouteServiceProvider::class,
            \Czim\CmsCore\Providers\MiddlewareServiceProvider::class,
            \Czim\CmsCore\Providers\MigrationServiceProvider::class,
            \Czim\CmsCore\Providers\ViewServiceProvider::class,
            //\Czim\CmsAuth\Providers\CmsAuthServiceProvider::class,
            //\Czim\CmsTheme\Providers\CmsThemeServiceProvider::class,
            //\Czim\CmsAuth\Providers\Api\OAuthSetupServiceProvider::class,
            \Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider::class,
            \Czim\CmsCore\Providers\Api\ApiRouteServiceProvider::class,
        ]);

        $app['config']->set('cms-api.providers', [
            //\Czim\CmsAuth\Providers\Api\FluentStorageServiceProvider::class,
            //\Czim\CmsAuth\Providers\Api\OAuth2ServerServiceProvider::class,
        ]);

        // Mock component bindings in the config
        $app['config']->set(
            'cms-core.bindings', [
                Component::BOOTCHECKER => $this->getTestBootCheckerBinding(),
                Component::CACHE       => \Czim\CmsCore\Core\Cache::class,
                Component::CORE        => \Czim\CmsCore\Core\Core::class,
                Component::MODULES     => \Czim\CmsCore\Modules\Manager\Manager::class,
                Component::AUTH        => \Czim\CmsAuth\Auth\Authenticator::class,
                Component::API         => \Czim\CmsCore\Api\ApiCore::class,
                Component::ACL         => \Czim\CmsCore\Auth\AclRepository::class,
                Component::MENU        => \Czim\CmsCore\Menu\MenuRepository::class,
                Component::AUTH        => 'mock-cms-auth',
        ]);



        $this->mockBoundCoreExternalComponents($app);

        $app->register(CmsCoreServiceProvider::class);
    }

    /**
     * @return string
     */
    protected function getTestBootCheckerBinding()
    {
        return \Czim\CmsCore\Core\BootChecker::class;
    }

    /**
     * Mocks components that should not be part of any core test.
     *
     * @param Application $app
     * @return $this
     */
    protected function mockBoundCoreExternalComponents(Application $app)
    {
        $app->bind('mock-cms-auth', function () {

            $mock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();

            $mock->method('version')->willReturn('1.2.3');

            $mock->method('getRouteLoginAction')->willReturn('MockController@index');
            $mock->method('getRouteLoginPostAction')->willReturn('MockController@index');
            $mock->method('getRouteLogoutAction')->willReturn('MockController@index');

            $mock->method('getRoutePasswordEmailGetAction')->willReturn('MockController@index');
            $mock->method('getRoutePasswordEmailPostAction')->willReturn('MockController@index');
            $mock->method('getRoutePasswordResetGetAction')->willReturn('MockController@index');
            $mock->method('getRoutePasswordResetPostAction')->willReturn('MockController@index');

            return $mock;
        });

        return $this;
    }

}
