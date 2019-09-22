<?php
namespace Czim\CmsCore\Test;

use Czim\CmsCore\Core\BootChecker;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Foundation\Application;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Mockery;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        /** @var Repository $config */
        $config = $app['config'];

        $config->set('cms-modules.modules', []);
    }

    protected function getTestBootCheckerBinding(): string
    {
        return BootChecker::class;
    }

    /**
     * Mocks components that should not be part of any core test.
     *
     * @param Application $app
     * @return $this
     */
    protected function mockBoundCoreExternalComponents(Application $app): TestCase
    {
        $app->bind('mock-cms-auth', function () {

            $mock = $this->getMockAuthenticator();

            $mock->shouldReceive('version')->andReturn('1.2.3');

            $mock->shouldReceive('admin')->andReturn(false);
            $mock->shouldReceive('user')->andReturn(null);

            $mock->shouldReceive('getRouteLoginAction')->andReturn('MockController@index');
            $mock->shouldReceive('getRouteLoginPostAction')->andReturn('MockController@index');
            $mock->shouldReceive('getRouteLogoutAction')->andReturn('MockController@index');

            $mock->shouldReceive('getApiRouteLoginAction')->andReturn('MockController@index');
            $mock->shouldReceive('getApiRouteLogoutAction')->andReturn('MockController@index');

            $mock->shouldReceive('getRoutePasswordEmailGetAction')->andReturn('MockController@index');
            $mock->shouldReceive('getRoutePasswordEmailPostAction')->andReturn('MockController@index');
            $mock->shouldReceive('getRoutePasswordResetGetAction')->andReturn('MockController@index');
            $mock->shouldReceive('getRoutePasswordResetPostAction')->andReturn('MockController@index');

            return $mock;
        });

        return $this;
    }

    /**
     * Returns most recent artisan command output.
     *
     * @return string
     */
    protected function getArtisanOutput(): string
    {
        return $this->app[ConsoleKernelContract::class]->output();
    }

    /**
     * @return \Mockery\MockInterface|\Mockery\Mock|AuthenticatorInterface
     */
    protected function getMockAuthenticator()
    {
        return Mockery::mock(AuthenticatorInterface::class);
    }


}
