<?php
namespace Czim\CmsCore\Test\Helpers\Listeners;

/**
 * Class TestEventListener
 *
 * @see \Czim\CmsCore\Test\Providers\EventServiceProviderTest
 */
class TestEventListener
{

    public function triggered()
    {
        // Bind something in the app to perform a simple assertion on in the test
        app()->instance('testing.listened', true);
    }

}
