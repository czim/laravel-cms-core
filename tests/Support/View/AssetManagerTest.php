<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Support\View;

use Czim\CmsCore\Support\View\AssetManager;
use Czim\CmsCore\Test\TestCase;

class AssetManagerTest extends TestCase
{

    /**
     * @test
     */
    function it_registers_style_assets()
    {
        $manager = new AssetManager();

        static::assertSame($manager, $manager->registerStyleAsset('/path/to/asset', 'something', 'screen'));

        static::assertEquals(
            '<link rel="stylesheet" href="/path/to/asset" type="something" media="screen">',
            $manager->renderStyleAssets()
        );

        // Without parameters
        $manager = new AssetManager();

        $manager->registerStyleAsset('/path/to/asset');

        static::assertEquals('<link rel="stylesheet" href="/path/to/asset">', $manager->renderStyleAssets());
    }

    /**
     * @test
     */
    function it_registers_script_footer_assets()
    {
        $manager = new AssetManager();

        static::assertSame($manager, $manager->registerScriptAsset('/path/to/asset'));
        $manager->registerScriptAsset('/not/footer', true);

        static::assertEquals('<script src="/path/to/asset"></script>', $manager->renderScriptAssets());
    }

    /**
     * @test
     */
    function it_registers_script_header_assets()
    {
        $manager = new AssetManager();

        static::assertSame($manager, $manager->registerScriptAsset('/path/to/asset', true));
        $manager->registerScriptAsset('/not/footer');

        static::assertEquals('<script src="/path/to/asset"></script>', $manager->renderScriptHeadAssets());
    }

    /**
     * @test
     */
    function it_registers_script_bodies()
    {
        $manager = new AssetManager();

        static::assertSame($manager, $manager->registerScript('<script>test</script>', false));
        $manager->registerScript('<script>test</script>', false);

        static::assertEquals("<script>test</script>\n<script>test</script>", $manager->renderScripts());
    }

    /**
     * @test
     */
    function it_registers_script_bodies_such_that_duplicates_are_not_rendered()
    {
        $manager = new AssetManager();

        static::assertSame($manager, $manager->registerScript('<script>test</script>'));
        $manager->registerScript('<script>test</script>');

        static::assertEquals('<script>test</script>', $manager->renderScripts());
    }

}
