<?php
namespace Czim\CmsCore\Test;

abstract class WebTestCase extends TestCase
{

    /**
     * @return string
     */
    protected function getTestBootCheckerBinding()
    {
        return \Czim\CmsCore\Test\Helpers\Core\MockWebBootChecker::class;
    }



    protected function getTestCoreConfig()
    {
        // todo
    }

}
