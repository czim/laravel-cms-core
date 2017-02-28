<?php
namespace Czim\CmsCore\Test\Support\Localization;

use Czim\CmsCore\Support\Localization\LocaleRepository;
use Czim\CmsCore\Test\TestCase;

class LocaleRepositoryTest extends TestCase
{

    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('app.locale', 'nl');
        $this->app['config']->set('app.fallback_locale', 'nl');
    }

    /**
     * @test
     */
    function it_returns_the_default_locale()
    {
        $repository = $this->makeLocaleRepository();

        static::assertEquals('nl', $repository->getDefault());
    }

    /**
     * @test
     */
    function it_returns_available_locales_without_localization_packages()
    {
        $repository = $this->makeLocaleRepository();

        // When default is the same as fallback locale
        static::assertEquals(['nl'], $repository->getAvailable());

        // When different fallback locale is set
        $this->app['config']->set('app.locale', 'en');

        static::assertEquals(['en', 'nl'], $repository->getAvailable());
    }

    /**
     * @test
     */
    function it_returns_available_locales_defined_in_mcamara_localization_package()
    {
        $repository = $this->makeLocaleRepository();

        $this->app['config']->set('laravellocalization.supportedLocales', [
            'ace' => ['name' => 'Achinese', 'script' => 'Latn', 'native' => 'Aceh', 'regional' => ''],
            'af'  => ['name' => 'Afrikaans', 'script' => 'Latn', 'native' => 'Afrikaans', 'regional' => 'af_ZA'],
            'agq' => ['name' => 'Aghem', 'script' => 'Latn', 'native' => 'Aghem', 'regional' => ''],
        ]);

        static::assertEquals(['ace', 'af', 'agq'], $repository->getAvailable());
    }

    /**
     * @test
     */
    function it_returns_whether_the_environment_is_localized()
    {
        $repository = $this->makeLocaleRepository();

        static::assertFalse($repository->isLocalized(), "Single locale should not be isLocalized");

        // When different fallback locale is set
        $this->app['config']->set('app.locale', 'en');

        static::assertTrue($repository->isLocalized(), "Double locale should nbe isLocalized");
    }

    /**
     * @test
     */
    function it_returns_whether_a_locale_is_available()
    {
        $repository = $this->makeLocaleRepository();
        
        static::assertTrue($repository->isAvailable('nl'));
        static::assertFalse($repository->isAvailable('it'));
    }


    /**
     * @return LocaleRepository
     */
    protected function makeLocaleRepository()
    {
        return app(LocaleRepository::class);
    }

}
