<?php
namespace Czim\CmsCore\Test;

use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Foundation\Application;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

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

    /**
     * Returns most recent artisan command output.
     *
     * @return string
     */
    protected function getArtisanOutput()
    {
        return $this->app[ConsoleKernelContract::class]->output();
    }

}
