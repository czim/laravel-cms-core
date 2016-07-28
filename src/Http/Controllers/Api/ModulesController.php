<?php
namespace Czim\CmsCore\Http\Controllers\Api;

use Czim\CmsCore\Api\Response\TransformContainer;
use Czim\CmsCore\Api\Transformers\ModuleTransformer;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ManagerInterface;
use Czim\CmsCore\Http\Controllers\Controller;

class ModulesController extends Controller
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var ManagerInterface
     */
    protected $modules;

    /**
     * @param CoreInterface    $core
     * @param ManagerInterface $modules
     */
    public function __construct(CoreInterface $core, ManagerInterface $modules)
    {
        $this->core    = $core;
        $this->modules = $modules;
    }


    /**
     * Returns a list of modules currently loaded.
     *
     * @return mixed
     */
    public function index()
    {
        return $this->core->api()->response(
            $this->makeContainer($this->modules->getModules())
        );
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function show($key)
    {
        $module = $this->modules->get($key);

        if ( ! $module) {
            abort(404, "Module not found or not loaded");
        }

        return $this->core->api()->response(
            $this->makeContainer($module, false)
        );
    }


    /**
     * Wraps data in a transform container.
     *
     * @param array $data
     * @param bool  $collection
     * @return TransformContainer
     */
    protected function makeContainer($data, $collection = true)
    {
        return new TransformContainer([
            'content'     => $data,
            'transformer' => new ModuleTransformer,
            'collection'  => $collection,
        ]);
    }

}
