<?php
namespace Czim\CmsCore\Contracts\Support\View;

interface AssetManagerInterface
{

    /**
     * Registers a CMS stylesheet asset.
     *
     * @param string      $path
     * @param null|string $type
     * @param null|string $media
     * @param string      $rel
     * @return $this
     */
    public function registerStyleAsset($path, $type = null, $media = null, $rel = 'stylesheet');

    /**
     * Registers a CMS javascript asset by (relative) path.
     *
     * @param string $path
     * @param bool   $head      whether to add the asset to the head
     * @return $this
     */
    public function registerScriptAsset($path, $head = false);

    /**
     * Registers CMS javascript code
     *
     * @param      $script
     * @param bool $once        if true, only registers script with these exact contents once
     * @return $this
     */
    public function registerScript($script, $once = true);

    /**
     * Returns rendered registered stylesheet asset links.
     *
     * @return string
     */
    public function renderStyleAssets();

    /**
     * Returns rendered registered script asset links for the footer.
     *
     * @return string
     */
    public function renderScriptAssets();

    /**
     * Returns rendered registered script asset links for the header.
     *
     * @return string
     */
    public function renderScriptHeadAssets();

    /**
     * Returns rendered registered scripts.
     *
     * @return string
     */
    public function renderScripts();

}
