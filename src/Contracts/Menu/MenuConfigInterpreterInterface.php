<?php
namespace Czim\CmsCore\Contracts\Menu;

use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;

interface MenuConfigInterpreterInterface
{

    /**
     * Interprets a configured menu layout array.
     *
     * @param array $layout
     * @return MenuLayoutDataInterface
     */
    public function interpretLayout(array $layout);

}
