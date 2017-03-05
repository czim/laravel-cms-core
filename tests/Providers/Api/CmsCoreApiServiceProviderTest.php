<?php
namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Api\Response\ResponseBuilderInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider;
use Czim\CmsCore\Providers\EventServiceProvider;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Contracts\Foundation\Application;

class CmsCoreApiServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    function it_registers_interfaces_and_service_providers()
    {
        /** @var Application|\PHPUnit_Framework_MockObject_MockObject $appMock */
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var BootCheckerInterface|\PHPUnit_Framework_MockObject_MockObject $checkerMock */
        $appMock     = $this->getMockBuilder(Application::class)->getMock();
        $coreMock    = $this->getMockBuilder(CoreInterface::class)->getMock();
        $checkerMock = $this->getMockBuilder(BootCheckerInterface::class)->getMock();

        $coreMock->method('apiConfig')
            ->willReturnCallback(function ($key, $default = null) {
                switch ($key) {
                    case 'response-builder':
                        return 'test-builder';
                    case 'providers':
                        return [EventServiceProvider::class];
                }
                return $default;
            });

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldCmsApiRegister')
            ->willReturn(true);

        $appMock->expects(static::once())
            ->method('singleton')
            ->with(ResponseBuilderInterface::class);

        $appMock->expects(static::once())
            ->method('register')
            ->with(EventServiceProvider::class);

        $provider = new CmsCoreApiServiceProvider($appMock);
        $provider->register($coreMock, $checkerMock);
    }

    /**
     * @test
     */
    function it_skips_registration_if_bootcheckers_says_to()
    {
        /** @var Application|\PHPUnit_Framework_MockObject_MockObject $appMock */
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var BootCheckerInterface|\PHPUnit_Framework_MockObject_MockObject $checkerMock */
        $appMock     = $this->getMockBuilder(Application::class)->getMock();
        $coreMock    = $this->getMockBuilder(CoreInterface::class)->getMock();
        $checkerMock = $this->getMockBuilder(BootCheckerInterface::class)->getMock();

        $checkerMock->expects(static::atLeastOnce())
            ->method('shouldCmsApiRegister')
            ->willReturn(false);

        $appMock->expects(static::never())->method('singleton');
        $appMock->expects(static::never())->method('register');

        $provider = new CmsCoreApiServiceProvider($appMock);
        $provider->register($coreMock, $checkerMock);
    }

}
