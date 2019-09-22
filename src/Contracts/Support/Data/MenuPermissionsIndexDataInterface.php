<?php
namespace Czim\CmsCore\Contracts\Support\Data;

use Czim\DataObject\Contracts\DataObjectInterface;

interface MenuPermissionsIndexDataInterface extends DataObjectInterface
{

    /**
     * Returns the permissions index.
     *
     * @return array    associative: normalized nested node key => list of permissions
     */
    public function index(): array;

    /**
     * Returns a list of all permissions referenced in the index.
     *
     * @return string[]
     */
    public function permissions(): array;

    /**
     * Returns indexed permissions for a given menu layout node.
     *
     * @param array $nodeKey
     * @return string[]|false       false if the node is not present in the index
     */
    public function getForNode(array $nodeKey);

    /**
     * Sets permissions for a given menu layout node.
     *
     * @param array    $nodeKey
     * @param string[] $permissions
     * @return $this
     */
    public function setForNode(array $nodeKey, array $permissions = []): MenuPermissionsIndexDataInterface;

    /**
     * Normalizes an array of keys leading to a node in the layout tree to a single string.
     *
     * @param array $nodeKey
     * @return string
     */
    public function stringifyNodeKey(array $nodeKey): string;

}
