<?php
namespace Czim\CmsCore\Test;

use Czim\CmsAuthApi\Http\Middleware\OAuthMiddleware;
use Czim\CmsAuthApi\Http\Middleware\OAuthUserOwnerMiddleware;
use Czim\CmsCore\Test\Helpers\Core\MockApiBootChecker;
use Czim\CmsCore\Test\Helpers\Http\NullMiddleware;
use Illuminate\Foundation\Application;

abstract class ApiTestCase extends WebTestCase
{

    /**
     * Define environment setup.
     *
     * @param Application $app
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->bindNullMiddleware($app);
    }

    protected function getTestBootCheckerBinding(): string
    {
        return MockApiBootChecker::class;
    }

    /**
     * Binds inert middleware to standard API middleware not included in this package.
     *
     * @param Application $app
     */
    protected function bindNullMiddleware(Application $app): void
    {
        $app->bind(
            OAuthMiddleware::class,
            NullMiddleware::class
        );

        $app->bind(
            OAuthUserOwnerMiddleware::class,
            NullMiddleware::class
        );
    }

}
