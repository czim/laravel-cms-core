<?php
namespace Czim\CmsCore\Test\Helpers;

use Czim\CmsCore\Core\BootChecker;

class MockApiBootChecker extends BootChecker
{

    /**
     * {@inheritdoc}
     */
    public function shouldRegisterCmsApiRoutes()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldLoadCmsApiMiddleware()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function shouldCmsApiRegister()
    {
        return true;
    }

}
