<?php
namespace Czim\CmsCore\Test\Support;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Contracts\Routing\UrlGenerator;
use Symfony\Component\Translation\TranslatorInterface;

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
        $this->app->instance(Component::CORE, 'testing');

        static::assertEquals('testing', cms());
    }
    
    /**
     * @test
     */
    function its_cms_route_function_returns_a_prefixed_route()
    {
        $urlMock = $this->getMockBuilder(UrlGenerator::class)->getMock();
        $urlMock->expects(static::once())->method('route')->with('cms::testing-something');

        $this->app->instance('url', $urlMock);

        cms_route('testing-something');
    }

    /**
     * @test
     */
    function its_cms_api_function_returns_the_api_core()
    {
        $this->app->instance(Component::API, 'testing');

        static::assertEquals('testing', cms_api());
    }

    /**
     * @test
     */
    function its_cms_auth_function_returns_the_authenticator()
    {
        $this->app->instance(Component::AUTH, 'testing');

        static::assertEquals('testing', cms_auth());
    }

    /**
     * @test
     */
    function its_cms_config_function_returns_a_core_config_value()
    {
        $coreMock = $this->getMockBuilder(CoreInterface::class)->getMock();
        $coreMock->expects(static::once())->method('config')->with('test-key', 'test-default');

        $this->app->instance(Component::CORE, $coreMock);

        cms_config('test-key', 'test-default');
    }

    /**
     * @test
     */
    function its_cms_mw_permission_function_returns_a_permission_prefixed_middleware_string()
    {
        static::assertEquals(
            \Czim\CmsCore\Support\Enums\CmsMiddleware::PERMISSION  . ':testing-permission',
            cms_mw_permission('testing-permission')
        );
    }

    /**
     * @test
     */
    function its_cms_flash_config_function_relays_a_flash_message_to_the_notifier()
    {
        $notifierMock = $this->getMockBuilder(NotifierInterface::class)->getMock();
        $notifierMock->expects(static::once())->method('flash')->with('test-message', 'emergency');

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
        $transMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['has', 'trans', 'transChoice', 'getLocale', 'setLocale'])
            ->getMock();

        $transMock->expects(static::once())
            ->method('trans')
            ->with('cms::test-id', ['testparam' => 'value'], 'test-messages', 'en');

        $transMock->method('has')->willReturn(true);

        $this->app->instance('translator', $transMock);

        cms_trans('test-id', ['testparam' => 'value'], 'test-messages', 'en');
    }

    /**
     * @test
     */
    function its_cms_trans_function_falls_back_to_app_translation_if_cms_translation_does_not_exist()
    {
        $transMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['has', 'trans', 'transChoice', 'getLocale', 'setLocale'])
            ->getMock();

        $transMock->expects(static::once())
            ->method('trans')
            ->with('test-id', ['testparam' => 'value'], 'test-messages', 'en');

        $transMock->method('has')->willReturn(false);

        $this->app->instance('translator', $transMock);

        cms_trans('test-id', ['testparam' => 'value'], 'test-messages', 'en');
    }

    /**
     * @test
     */
    function its_cms_trans_choice_function_returns_prefixed_translation()
    {
        $transMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['has', 'trans', 'transChoice', 'getLocale', 'setLocale'])
            ->getMock();

        $transMock->expects(static::once())
            ->method('transChoice')
            ->with('cms::test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');

        $transMock->method('has')->willReturn(true);

        $this->app->instance('translator', $transMock);

        cms_trans_choice('test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');
    }

    /**
     * @test
     */
    function its_cms_trans_choice_function_falls_back_to_app_translation_if_cms_translation_does_not_exist()
    {
        $transMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['has', 'trans', 'transChoice', 'getLocale', 'setLocale'])
            ->getMock();

        $transMock->expects(static::once())
            ->method('transChoice')
            ->with('test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');

        $transMock->method('has')->willReturn(false);

        $this->app->instance('translator', $transMock);

        cms_trans_choice('test-id', 2, ['testparam' => 'value'], 'test-messages', 'en');
    }

}
