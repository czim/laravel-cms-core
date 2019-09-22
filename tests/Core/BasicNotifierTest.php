<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Core\BasicNotifier;
use Czim\CmsCore\Test\Helpers\Spies\SessionSpy;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Session\SessionManager;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class BasicNotifierTest extends CmsBootTestCase
{
    /**
     * @var SessionManager
     */
    protected $sessionMock;

    /**
     * @test
     */
    function it_can_flash_a_message_to_the_session()
    {
        $this->sessionMock = $this->getSessionSpy();

        $notifier = $this->makeNotifier();

        static::assertEmpty($notifier->getFlashed(), 'no messages should be in flash memory on init');

        $notifier->flash('testing message');

        $flashed = $notifier->getFlashed();

        static::assertIsArray($flashed);
        static::assertCount(1, $flashed);
        static::assertEquals(['level' => 'info', 'message' => 'testing message'], head($flashed));
    }

    /**
     * @test
     */
    function it_clears_flash_messages_after_retrieving_by_default()
    {
        $this->sessionMock = $this->getSessionSpy();

        $notifier = $this->makeNotifier();

        $notifier->flash('testing message');

        static::assertCount(1, $notifier->getFlashed());
        static::assertCount(0, $notifier->getFlashed(), 'Flash message was not cleared');
    }

    /**
     * @test
     */
    function it_optionally_does_not_clear_flash_messages_after_retrieving()
    {
        $this->sessionMock = $this->getSessionSpy();

        $notifier = $this->makeNotifier();

        $notifier->flash('testing message');

        static::assertCount(1, $notifier->getFlashed(false));
        static::assertCount(1, $notifier->getFlashed(), 'Flash message was cleared');
    }


    protected function makeNotifier(): BasicNotifier
    {
        return new BasicNotifier($this->getMockCore());
    }

    /**
     * @return MockInterface|Mock|CoreInterface $mockCore
     */
    protected function getMockCore()
    {
        /** @var MockInterface|Mock|CoreInterface $mockCore */
        $mockCore = Mockery::mock(CoreInterface::class);

        $mockCore->shouldReceive('session')->andReturn($this->sessionMock);

        return $mockCore;
    }

    protected function getSessionSpy(): SessionSpy
    {
        return new SessionSpy;
    }

}

