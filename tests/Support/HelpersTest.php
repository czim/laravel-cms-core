<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Support;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;
use Czim\CmsCore\Support\Enums\CmsMiddleware;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Translation\Translator;
use Mockery;

/**
 * Class HelpersTest
 *
 * Test for content in the support helpers.php file.
 */
class HelpersTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function its_cms_function_returns_the_core()
    {
        $core = Mockery::mock(CoreInterface::class);
        $this->app->instance(Component::CORE, $core);

        static::assertSame($core, cms());
    }

    /**
     * @test
     */
    function its_cms_route_function_returns_a_prefixed_route()
    {
        $urlMock = Mockery::mock(UrlGenerator::class);
        $urlMock->shouldReceive('route')->once()->with('cms::testing-something', [], true)
            ->andReturn('test');

        $this->app->instance('url', $urlMock);

        cms_route('testing-something');
    }

    /**
     * @test
     */
    function its_cms_api_function_returns_the_api_core()
    {
        $core = Mockery::mock(ApiCoreInterface::class);
        $this->app->instance(Component::API, $core);

        static::assertSame($core, cms_api());
    }

    /**
     * @test
     */
    function its_cms_auth_function_returns_the_authenticator()
    {
        $instance = Mockery::mock(AuthenticatorInterface::class);
        $this->app->instance(Component::AUTH, $instance);

        static::assertSame($instance, cms_auth());
    }

    /**
     * @test
     */
    function its_cms_config_function_returns_a_core_config_value()
    {
        $coreMock = Mockery::mock(CoreInterface::class);
        $coreMock->shouldReceive('config')->once()->with('test-key', 'test-default');

        $this->app->instance(Component::CORE, $coreMock);

        cms_config('test-key', 'test-default');
    }

    /**
     * @test
     */
    function its_cms_mw_permission_function_returns_a_permission_prefixed_middleware_string()
    {
        static::assertEquals(
            CmsMiddleware::PERMISSION  . ':testing-permission',
            cms_mw_permission('testing-permission')
        );
    }

    /**
     * @test
     */
    function its_cms_flash_config_function_relays_a_flash_message_to_the_notifier()
    {
        $notifierMock = Mockery::mock(NotifierInterface::class);
        $notifierMock->shouldReceive('flash')->once()->with('test-message', 'emergency');

        $this->app->instance(Component::NOTIFIER, $notifierMock);

        cms_flash('test-message', 'emergency');
    }

    /**
     * @test
     */
    function its_cms_trans_function_returns_the_translator_if_no_parameters_are_given()
    {
        $this->app->instance('translator', 'testing');

        static::assertEquals('testing', cms_trans());
    }

    /**
     * @test
     */
    function its_cms_trans_function_returns_prefixed_translation()
    {
        $transMock = Mockery::mock(Translator::class);
        $transMock->shouldReceive('has')->andReturn(true);
        $transMock->shouldReceive('get')->once()
            ->with('cms::test-id', ['testparam' => 'value'], 'test-messages', 'en');

        $this->app->instance('translator', $transMock);

        cms_trans('test-id', ['testparam' => 'value'], 'test-messages', 'en');
    }

    /**
     * @test
     */
    function its_cms_trans_function_falls_back_to_app_translation_if_cms_translation_does_not_exist()
    {
        $transMock = Mockery::mock(Translator::class);
        $transMock->shouldReceive('has')->andReturn(false);
        $transMock->shouldReceive('get')->once()
            ->with('test-id', ['testparam' => 'value'], 'test-messages', 'en');

        $this->app->instance('translator', $transMock);

        cms_trans('test-id', ['testparam' => 'value'], 'test-messages', 'en');
    }

    /**
     * @test
     */
    function its_cms_trans_choice_function_returns_prefixed_translation()
    {
        $transMock = Mockery::mock(Translator::class);
        $transMock->shouldReceive('has')->andReturn(true);
        $transMock->shouldReceive('choice')->once()
            ->with('cms::test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');

        $this->app->instance('translator', $transMock);

        cms_trans_choice('test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');
    }

    /**
     * @test
     */
    function its_cms_trans_choice_function_falls_back_to_app_translation_if_cms_translation_does_not_exist()
    {
        $transMock = Mockery::mock(Translator::class);
        $transMock->shouldReceive('has')->andReturn(false);
        $transMock->shouldReceive('choice')->once()
            ->with('test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');

        $this->app->instance('translator', $transMock);

        cms_trans_choice('test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');
    }

}
