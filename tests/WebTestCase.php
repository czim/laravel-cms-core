<?php
namespace Czim\CmsCore\Test;

use Czim\CmsCore\Test\Helpers\Core\MockWebBootChecker;

abstract class WebTestCase extends CmsBootTestCase
{

    protected function getTestBootCheckerBinding(): string
    {
        return MockWebBootChecker::class;
    }

}
