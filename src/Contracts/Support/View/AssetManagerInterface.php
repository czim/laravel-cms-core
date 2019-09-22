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
    public function registerStyleAsset(
        string $path,
        ?string $type = null,
        ?string $media = null,
        string $rel = 'stylesheet'
    ): AssetManagerInterface;

    /**
     * Registers a CMS javascript asset by (relative) path.
     *
     * @param string $path
     * @param bool   $head      whether to add the asset to the head
     * @return $this
     */
    public function registerScriptAsset(string $path, bool $head = false): AssetManagerInterface;

    /**
     * Registers CMS javascript code
     *
     * @param string  $script
     * @param bool    $once        if true, only registers script with these exact contents once
     * @return $this
     */
    public function registerScript(string $script, bool $once = true): AssetManagerInterface;

    /**
     * Returns rendered registered stylesheet asset links.
     *
     * @return string
     */
    public function renderStyleAssets(): string;

    /**
     * Returns rendered registered script asset links for the footer.
     *
     * @return string
     */
    public function renderScriptAssets(): string;

    /**
     * Returns rendered registered script asset links for the header.
     *
     * @return string
     */
    public function renderScriptHeadAssets(): string;

    /**
     * Returns rendered registered scripts.
     *
     * @return string
     */
    public function renderScripts(): string;

}
