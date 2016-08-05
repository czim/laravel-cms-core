<?php
namespace Czim\CmsCore\Test;

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
     */
    protected function bindNullMiddleware($app)
    {
        $app->bind(
            \Czim\CmsAuth\Http\Middleware\Api\OAuthMiddleware::class,
            \Czim\CmsCore\Test\Http\NullMiddleware::class
        );

        $app->bind(
            \Czim\CmsAuth\Http\Middleware\Api\OAuthUserOwnerMiddleware::class,
            \Czim\CmsCore\Test\Http\NullMiddleware::class
        );
    }

}
