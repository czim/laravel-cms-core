<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Api\Response\ResponseBuilderInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Providers\Api\CmsCoreApiServiceProvider;
use Czim\CmsCore\Providers\EventServiceProvider;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;
use Hamcrest\Matchers;
use Illuminate\Contracts\Foundation\Application;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class CmsCoreApiServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    function it_registers_interfaces_and_service_providers()
    {
        /** @var Application|Mock|MockInterface $appMock */
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var BootCheckerInterface|Mock|MockInterface $checkerMock */
        $appMock     = Mockery::mock(Application::class);
        $coreMock    = Mockery::mock(CoreInterface::class);
        $checkerMock = Mockery::mock(BootCheckerInterface::class);

        $coreMock->shouldReceive('apiConfig')
            ->andReturnUsing(function ($key, $default = null) {
                switch ($key) {
                    case 'response-builder':
                        return 'test-builder';
                    case 'providers':
                        return [EventServiceProvider::class];
                }
                return $default;
            });

        $coreMock->shouldReceive('bootChecker')->andReturn($checkerMock);

        $checkerMock->shouldReceive('shouldCmsApiRegister')
            ->atLeast()->once()
            ->andReturn(true);

        $appMock->shouldReceive('singleton')
            ->once()
            ->with(ResponseBuilderInterface::class, Matchers::anything());

        $appMock->shouldReceive('register')
            ->once()
            ->with(EventServiceProvider::class);

        $this->app->instance(Component::CORE, $coreMock);


        $provider = new CmsCoreApiServiceProvider($appMock);
        $provider->register();
    }

    /**
     * @test
     */
    function it_skips_registration_if_bootcheckers_says_to()
    {
        /** @var Application|Mock|MockInterface $appMock */
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var BootCheckerInterface|Mock|MockInterface $checkerMock */
        $appMock     = Mockery::mock(Application::class);
        $coreMock    = Mockery::mock(CoreInterface::class);
        $checkerMock = Mockery::mock(BootCheckerInterface::class);

        $coreMock->shouldReceive('bootChecker')->andReturn($checkerMock);

        $checkerMock->shouldReceive('shouldCmsApiRegister')
            ->atLeast()->once()
            ->andReturn(false);

        $appMock->shouldReceive('singleton')->never();
        $appMock->shouldReceive('register')->never();

        $this->app->instance(Component::CORE, $coreMock);


        $provider = new CmsCoreApiServiceProvider($appMock);
        $provider->register();
    }

}
