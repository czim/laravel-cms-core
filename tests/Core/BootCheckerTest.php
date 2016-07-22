<?php
namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Core\BootChecker;
use Czim\CmsCore\Test\TestCase;

class BootCheckerTest extends TestCase
{

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered()
    {
        $checker = new BootChecker();

        $this->assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_booted()
    {
        $checker = new BootChecker();

        $this->assertTrue($checker->shouldCmsBoot());
    }

    /**
     * @test
     */
    function it_reports_the_cms_as_registered()
    {
        $checker = new BootChecker();

        $this->assertTrue($checker->isCmsRegistered());
    }

    /**
     * @test
     */
    function it_reports_the_cms_as_booted()
    {
        $checker = new BootChecker();

        $this->assertTrue($checker->isCmsBooted());
    }

}
