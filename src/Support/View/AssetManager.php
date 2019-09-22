<?php
namespace Czim\CmsCore\Support\View;

use Czim\CmsCore\Contracts\Support\View\AssetManagerInterface;
use Illuminate\Support\Arr;

/**
 * Class AssetManager
 *
 * Manager and container for assets to be included by the CMS theme.
 * This helps collect all assets required by various modules in one place,
 * and helps prevent redundant/duplicate content from being added to views.
 */
class AssetManager implements AssetManagerInterface
{

    /**
     * List of registered stylesheet assets.
     *
     * @var array   associative, key is asset path; value is array with optional media/type
     */
    protected $styleAssets = [];

    /**
     * List of registered script assets.
     *
     * @var array   associative, key is asset path
     */
    protected $scriptAssets = [];

    /**
     * List of registered scripts.
     *
     * @var array
     */
    protected $scripts = [];

    /**
     * Mapping of MD5 hashes for scripts by index, for checks whether scripts are added only once.
     *
     * @var array   associative: hash => index
     */
    protected $scriptHashes = [];


    /**
     * Registers a CMS stylesheet asset.
     *
     * @param string      $path
     * @param null|string $type
     * @param null|string $media
     * @param string      $rel
     * @return $this
     */
    public function registerStyleAsset(
        string $path,
        ?string $type = null,
        ?string $media = null,
        string $rel = 'stylesheet'
    ): AssetManagerInterface {

        if ( ! array_key_exists($path, $this->styleAssets)) {
            $this->styleAssets[ $path ] = [
                'type'  => $type,
                'media' => $media,
                'rel'   => $rel,
            ];
        }

        return $this;
    }

    /**
     * Registers a CMS javascript asset by (relative) path.
     *
     * @param string $path
     * @param bool   $head      whether to add the asset to the head
     * @return $this
     */
    public function registerScriptAsset(string $path, bool $head = false): AssetManagerInterface
    {
        if ( ! array_key_exists($path, $this->scriptAssets)) {
            $this->scriptAssets[ $path ] = $head;
        }

        return $this;
    }

    /**
     * Registers CMS javascript code
     *
     * @param string $script
     * @param bool   $once        if true, only registers script with these exact contents once
     * @return $this
     */
    public function registerScript(string $script, bool $once = true): AssetManagerInterface
    {
        if ( ! $once) {
            $this->scripts[] = $script;

            return $this;
        }

        // Make sure the script is added only once
        $hash = md5($script);

        if (array_key_exists($hash, $this->scriptHashes)) {
            return $this;
        }

        $this->scripts[] = $script;

        // Map the hash for adding the script only once
        end($this->scripts);
        $index = key($this->scripts);

        $this->scriptHashes[$hash] = $index;

        return $this;
    }

    /**
     * Returns rendered registered stylesheet asset links.
     *
     * @return string
     */
    public function renderStyleAssets(): string
    {
        return implode(
            "\n",
            array_map(
                function ($asset, $parameters) {
                    return '<link rel="' . e(Arr::get($parameters, 'rel')) . '" href="' . $asset . '"'
                        . (Arr::get($parameters, 'type') ? ' type="' . e(Arr::get($parameters, 'type')) . '"' : '')
                        . (Arr::get($parameters, 'media') ? ' media="' . e(Arr::get($parameters, 'media')) . '"' : '')
                        . '>';
                },
                array_keys($this->styleAssets),
                array_values($this->styleAssets)
            )
        );
    }

    /**
     * Returns rendered registered script asset links for the footer.
     *
     * @return string
     */
    public function renderScriptAssets(): string
    {
        return implode(
            "\n",
            array_map(
                function ($asset) {
                    return '<script src="' . $asset . '"></script>';
                },
                array_keys(
                    array_filter($this->scriptAssets, function ($head) { return ! $head; })
                )
            )
        );
    }

    /**
     * Returns rendered registered script asset links for the header.
     *
     * @return string
     */
    public function renderScriptHeadAssets(): string
    {
        return implode(
            "\n",
            array_map(
                function ($asset) {
                    return '<script src="' . $asset . '"></script>';
                },
                array_keys(
                    array_filter($this->scriptAssets)
                )
            )
        );
    }

    /**
     * Returns rendered registered scripts.
     *
     * @return string
     */
    public function renderScripts(): string
    {
        return implode("\n", $this->scripts);
    }

}
