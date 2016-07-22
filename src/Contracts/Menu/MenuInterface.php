<?php
namespace Czim\CmsCore\Contracts\Menu;

interface MenuInterface
{

    /**
     * Returns variables prepared for the menu partial view.
     *
     * @return array
     */
    public function composeMenuVariables();

    /**
     * Returns variables prepared for the top/header partial view.
     *
     * @return array
     */
    public function composeTopVariables();

}
