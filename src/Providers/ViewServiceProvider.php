<?php
namespace Czim\CmsCore\Providers;

use Blade;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\ViewComposers\LocaleComposer;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface $core
     */
    public function boot(CoreInterface $core)
    {
        $this->core = $core;

        $this
            ->registerViewComposers()
            ->registerBladeDirectives();
    }

    public function register()
    {
    }

    /**
     * Registers routes for the entire CMS.
     *
     * @return $this
     */
    protected function registerViewComposers()
    {
        view()->composer(
            $this->core->config('views.locale-switch'),
            LocaleComposer::class
        );

        return $this;
    }

    /**
     * Registers blade directives for the CMS.
     *
     * @codeCoverageIgnore
     */
    protected function registerBladeDirectives()
    {
        // Set up the cms_script directive
        Blade::directive('cms_script', function () {
            return '<?php ob_start(); ?>';
        });

        Blade::directive('cms_endscript', function () {
            return "<?php app(Czim\\CmsCore\\Contracts\\Support\\View\\AssetManagerInterface::class)"
                 . '->registerScript(ob_get_clean(), false); ?>';
        });


        // Set up the cms_scriptonce directive
        Blade::directive('cms_scriptonce', function () {
            return '<?php ob_start(); ?>';
        });

        Blade::directive('cms_endscriptonce', function () {
            return "<?php app(Czim\\CmsCore\\Contracts\\Support\\View\\AssetManagerInterface::class)"
                 . '->registerScript(ob_get_clean(), true); ?>';
        });


        // Set up the cms_scriptassethead directive
        Blade::directive('cms_scriptassethead', function ($expression) {

            if ($this->isBladeArgumentStringBracketed()) {
                $expression = substr($expression, 1, -1);
            }

            return "<?php app(Czim\\CmsCore\\Contracts\\Support\\View\\AssetManagerInterface::class)"
                . "->registerScriptAsset({$expression}, true); ?>";
        });


        // Set up the cms_scriptasset directive
        Blade::directive('cms_scriptasset', function ($expression) {
            return "<?php app(Czim\\CmsCore\\Contracts\\Support\\View\\AssetManagerInterface::class)"
                . '->registerScriptAsset'
                . ($this->isBladeArgumentStringBracketed() ? $expression : "({$expression})") . '; ?>';
        });


        // Set up the cms_style directive
        Blade::directive('cms_style', function ($expression) {
            return "<?php app(Czim\\CmsCore\\Contracts\\Support\\View\\AssetManagerInterface::class)"
                . '->registerStyleAsset'
                . ($this->isBladeArgumentStringBracketed() ? $expression : "({$expression})") . '; ?>';
        });
    }

    /**
     * Returns whether the blade directive argument brackets are included.
     *
     * This behaviour was changed with the release of Laravel 5.3.
     *
     * @return bool
     * @codeCoverageIgnore
     */
    protected function isBladeArgumentStringBracketed()
    {
        if ( ! preg_match('#^(?<major>\d+)\.(?<minor>\d+)(?:\..*)?$#', Application::VERSION, $matches)) {
            return true;
        }
        $major = (int) $matches['major'];
        $minor = (int) $matches['minor'];
        return ($major < 5 || $major === 5 && $minor < 3);
    }

}
