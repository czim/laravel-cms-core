<?php
namespace Czim\CmsCore\Http\Controllers\Api;

use Czim\CmsCore\Api\Response\TransformContainer;
use Czim\CmsCore\Api\Transformers\VersionTransformer;
use Czim\CmsCore\Http\Controllers\Controller;

class VersionController extends Controller
{

    /**
     * Returns a list of components with current version numbers.
     *
     * @return mixed
     */
    public function index()
    {
        return $this->core->api()->response(
            $this->makeContainer($this->getVersions())
        );
    }

    /**
     * @return array
     */
    protected function getVersions()
    {
        return [
            'core'    => $this->core->version(),
            'auth'    => $this->core->auth()->version(),
            'modules' => $this->core->modules()->version(),
        ];
    }

    /**
     * Wraps data in a transform container.
     *
     * @param array $data
     * @param bool  $collection
     * @return TransformContainer
     */
    protected function makeContainer($data, $collection = false)
    {
        return new TransformContainer([
            'content'     => $data,
            'transformer' => new VersionTransformer(),
            'collection'  => $collection,
        ]);
    }

}
