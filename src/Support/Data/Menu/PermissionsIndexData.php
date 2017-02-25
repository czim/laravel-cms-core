<?php
namespace Czim\CmsCore\Support\Data\Menu;

use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class PermissionsIndexData
 *
 * Data container that has all the relevant data to filter menu layout on permissions.
 *
 * @property array    $index
 * @property string[] $permissions
 */
class PermissionsIndexData extends AbstractDataObject implements MenuPermissionsIndexDataInterface
{
    const KEY_TOP_LEVEL = '*';
    const KEY_SEPARATOR = '.';

    protected $attributes = [
        // The permissions index
        'index' => [],
        // All permissions referenced
        'permissions' => [],
    ];

    /**
     * Returns the permissions index.
     *
     * @return array    associative: normalized nested node key => list of permissions
     */
    public function index()
    {
        return $this->getAttribute('index') ?: [];
    }

    /**
     * Returns a list of all permissions referenced in the index.
     *
     * @return string[]
     */
    public function permissions()
    {
        return $this->getAttribute('permissions') ?: [];
    }

    /**
     * Returns indexed permissions for a given menu layout node.
     *
     * @param array $nodeKey
     * @return string[]|false       false if the node is not present in the index
     */
    public function getForNode(array $nodeKey)
    {
        $normalized = $this->stringifyNodeKey($nodeKey);

        return array_get($this->index(), $normalized, false);
    }

    /**
     * Sets permissions for a given menu layout node.
     *
     * @param array    $nodeKey
     * @param string[] $permissions
     * @return $this
     */
    public function setForNode(array $nodeKey, array $permissions = [])
    {
        $this->attributes['index'][ $this->stringifyNodeKey($nodeKey) ] = $permissions;

        return $this;
    }

    /**
     * Normalizes an array of keys leading to a node in the layout tree to a single string.
     *
     * @param array $nodeKey
     * @return string
     */
    public function stringifyNodeKey(array $nodeKey)
    {
        if ( ! count($nodeKey)) {
            return static::KEY_TOP_LEVEL;
        }

        return implode(static::KEY_SEPARATOR, array_map('base64_encode', $nodeKey));
    }

}
