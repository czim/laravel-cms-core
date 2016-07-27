<?php
namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;

class BootCheckerTest extends TestCase
{

    /**
     * @test
     */
    function it_reports_the_cms_should_be_registered()
    {
        $checker = $this->getBootChecker();

        $this->assertTrue($checker->shouldCmsRegister());
    }

    /**
     * @test
     */
    function it_reports_the_cms_should_be_booted()
    {
        $checker = $this->getBootChecker();

        $this->assertTrue($checker->shouldCmsBoot());
    }

    /**
     * @test
     */
    function it_reports_the_cms_as_registered()
    {
        $checker = $this->getBootChecker();

        $this->assertTrue($checker->isCmsRegistered());
    }

    /**
     * @test
     */
    function it_reports_the_cms_as_booted()
    {
        $checker = $this->getBootChecker();

        $this->assertTrue($checker->isCmsBooted());
    }

    /**
     * @test
     */
    function it_reports_whether_middleware_should_be_loaded()
    {
        $checker = $this->getBootChecker();

        $this->assertFalse($checker->shouldLoadCmsApiMiddleware(), "Should not load middleware for testing");
    }

    /**
     * @test
     */
    function it_reports_whether_a_request_is_a_web_request()
    {
        $checker = $this->getBootChecker();

        $this->assertFalse($checker->isCmsWebRequest(), "Test env. is not a web request");
    }

    /**
     * @test
     */
    function it_reports_whether_a_request_is_for_the_api()
    {
        $checker = $this->getBootChecker();

        $this->assertFalse($checker->isCmsApiRequest(), "Test env. is not the API");
    }

    /**
     * @return BootCheckerInterface
     */
    protected function getBootChecker()
    {
        return app(Component::BOOTCHECKER);
    }

}
