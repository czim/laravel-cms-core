<?php
namespace Czim\CmsCore\Test;

abstract class WebTestCase extends CmsBootTestCase
{

    /**
     * @return string
     */
    protected function getTestBootCheckerBinding()
    {
        return \Czim\CmsCore\Test\Helpers\Core\MockWebBootChecker::class;
    }

}
