<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Providers\EventServiceProvider;
use Czim\CmsCore\Test\TestCase;

class EventServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    function it_registers_configured_events()
    {
        $this->app['config']->set('cms-core.events', [
            'testing.event.trigger' => [
                \Czim\CmsCore\Test\Helpers\Listeners\TestEventListener::class . '@triggered',
            ],
        ]);

        $provider = new EventServiceProvider($this->app);
        $provider->register();
        $provider->boot();

        event('testing.event.trigger');

        static::assertTrue($this->app->bound('testing.listened'), 'Event listener did not catch the test event');
    }

}
