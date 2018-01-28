<?php
namespace Czim\CmsCore\Test;

use Illuminate\Foundation\Application;

abstract class ApiTestCase extends WebTestCase
{

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->bindNullMiddleware($app);
    }

    /**
     * @return string
     */
    protected function getTestBootCheckerBinding()
    {
        return \Czim\CmsCore\Test\Helpers\Core\MockApiBootChecker::class;
    }

    /**
     * Binds inert middleware to standard API middleware not included in this package.
     *
     * @param Application $app
     */
    protected function bindNullMiddleware(Application $app)
    {
        $app->bind(
            \Czim\CmsAuthApi\Http\Middleware\OAuthMiddleware::class,
            \Czim\CmsCore\Test\Helpers\Http\NullMiddleware::class
        );

        $app->bind(
            \Czim\CmsAuthApi\Http\Middleware\OAuthUserOwnerMiddleware::class,
            \Czim\CmsCore\Test\Helpers\Http\NullMiddleware::class
        );
    }

}
