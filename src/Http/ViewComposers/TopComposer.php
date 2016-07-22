<?php
namespace Czim\CmsCore\Http\ViewComposers;

use Illuminate\Contracts\View\View;
use Czim\CmsCore\Contracts\Menu\MenuInterface;

class TopComposer
{

    /**
     * @var MenuInterface
     */
    protected $menu;

    /**
     * @param MenuInterface $menu
     */
    public function __construct(MenuInterface $menu)
    {
        $this->menu = $menu;
    }

    /**
     * Bind data to the view.
     *
     * @param View $view
     */
    public function compose(View $view)
    {
        $view->with(
            $this->menu->composeTopVariables()
        );
    }

}
