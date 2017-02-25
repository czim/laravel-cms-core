<?php
namespace Czim\CmsCore\Contracts\Menu;

use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;

interface MenuModulesInterpreterInterface
{

    /**
     * Interprets configured module presences and returns a normalized data object.
     *
     * @return MenuConfiguredModulesDataInterface
     */
    public function interpret();

}
