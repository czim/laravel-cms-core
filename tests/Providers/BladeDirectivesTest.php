<?php
namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\View\AssetManagerInterface;
use Czim\CmsCore\Providers\ViewServiceProvider;
use Czim\CmsCore\Test\Helpers\Providers\TestViewsServiceProvider;
use Czim\CmsCore\Test\TestCase;
use Mockery;

class BladeDirectivesTest extends TestCase
{
    /**
     * @var AssetManagerInterface|Mockery\Mock
     */
    protected $assetManager;

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $this->assetManager = Mockery::mock(AssetManagerInterface::class);

        /** @var CoreInterface|Mockery\Mock $core */
        $core = Mockery::mock(CoreInterface::class);
        $core->shouldReceive('assets')->andReturn($this->assetManager);
        $core->shouldReceive('config')->andReturnUsing(function () { return func_get_args(2); });

        $app->instance(CoreInterface::class, $core);
        $app->instance(AssetManagerInterface::class, $this->assetManager);

        $app->register(TestViewsServiceProvider::class);
        $app->register(ViewServiceProvider::class);
    }

    /**
     * @test
     */
    function it_features_cms_style_directive()
    {
        $this->assetManager->shouldReceive('registerStyleAsset')->with('/testing/path')->once();

        view('cms-test::blade_cms_style')->render();
    }

    /**
     * @test
     */
    function it_features_cms_scriptasset_directive()
    {
        $this->assetManager->shouldReceive('registerScriptAsset')->with('/testing/path')->once();

        view('cms-test::blade_cms_scriptasset')->render();
    }

    /**
     * @test
     */
    function it_features_cms_scriptassethead_directive()
    {
        $this->assetManager->shouldReceive('registerScriptAsset')->with('/testing/path', true)->once();

        view('cms-test::blade_cms_scriptassethead')->render();
    }

    /**
     * @test
     */
    function it_features_cms_script_block_directive()
    {
        $this->assetManager->shouldReceive('registerScript')->with("testing!\n", false)->once();

        view('cms-test::blade_cms_script')->render();
    }

    /**
     * @test
     */
    function it_features_cms_scriptonce_block_directive()
    {
        $this->assetManager->shouldReceive('registerScript')->with("testing!\n", true)->once();

        view('cms-test::blade_cms_scriptonce')->render();
    }

}
