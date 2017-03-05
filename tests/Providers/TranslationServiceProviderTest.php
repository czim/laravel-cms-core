<?php
namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Providers\TranslationServiceProvider;
use Czim\CmsCore\Support\Translation\CmsTranslator;
use Czim\CmsCore\Test\TestCase;

class TranslationServiceProviderTest extends TestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        // Overridden to prevent CMS booting
    }

    /**
     * @test
     */
    function it_registers_the_cms_translator()
    {
        $provider = new TranslationServiceProvider($this->app);
        $provider->boot();

        static::assertTrue($this->app->bound('translator'));
        static::assertInstanceOf(CmsTranslator::class, $this->app['translator']);
    }

    /**
     * @test
     */
    function it_reports_providing_translator()
    {
        $provider = new TranslationServiceProvider($this->app);

        static::assertEquals(['translator'], $provider->provides());
    }

}
