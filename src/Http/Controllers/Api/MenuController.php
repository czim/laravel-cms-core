<?php
namespace Czim\CmsCore\Http\Controllers\Api;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Http\Controllers\Controller;
use Illuminate\Support\Collection;

class MenuController extends Controller
{

    /**
     * @var MenuRepositoryInterface
     */
    protected $menu;


    public function __construct(CoreInterface $core, MenuRepositoryInterface $menu)
    {
        parent::__construct($core);

        $this->menu = $menu;

        // For web routes, the menu structure is initialized by middleware;
        // here we must initialize it on-demand for menu-specific requests.
        $this->menu->initialize();
    }


    /**
     * Returns a list of modules currently loaded.
     *
     * @return mixed
     */
    public function index()
    {
        return $this->core->api()->response(
            [
                'layout' => $this->transformPresencesForApi($this->menu->getMenuLayout()),
            ]
        );
    }

    /**
     * Transforms menu presence tree for API response.
     *
     * @param array|Collection|MenuPresenceInterface[] $presences
     * @return array
     */
    protected function transformPresencesForApi($presences): array
    {
        $response = [];

        foreach ($presences as $presence) {
            $response[] = $this->transformPresenceForApi($presence);
        }

        return $response;
    }

    /**
     * @param MenuPresenceInterface $presence
     * @return array
     */
    protected function transformPresenceForApi(MenuPresenceInterface $presence): array
    {
        $response = [
            'id'          => $presence->id(),
            'type'        => $presence->type(),
            'label'       => $this->makeLabelForPresence($presence),
            'icon'        => $presence->icon(),
            'action'      => $this->makeLinkForPresence($presence),
            'parameters'  => $presence->parameters(),
            'permissions' => $presence->permissions(),
            'children'    => [],
        ];

        if ($presence->children() && count($presence->children())) {
            $response['children'] = $this->transformPresencesForApi($presence->children());
        }

        return $response;
    }

    /**
     * @param MenuPresenceInterface $presence
     * @return string|null
     */
    protected function makeLabelForPresence(MenuPresenceInterface $presence): ?string
    {
        return $presence->label();
    }

    /**
     * @param MenuPresenceInterface $presence
     * @return string|null
     */
    protected function makeLinkForPresence(MenuPresenceInterface $presence): ?string
    {
        return $presence->action();
    }

}
