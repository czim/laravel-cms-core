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


    protected function getVersions(): array
    {
        return [
            [
                'id'      => 1,
                'name'    => 'core',
                'version' => $this->core->version(),
            ],
            [
                'id'      => 2,
                'name'    => 'auth',
                'version' => $this->core->auth()->version(),
            ],
            [
                'id'      => 3,
                'name'    => 'module-manager',
                'version' => $this->core->modules()->version(),
            ],
        ];
    }

    /**
     * Wraps data in a transform container.
     *
     * @param array $data
     * @param bool  $collection
     * @return TransformContainer
     */
    protected function makeContainer($data, bool $collection = true): TransformContainer
    {
        return new TransformContainer([
            'content'     => $data,
            'transformer' => new VersionTransformer(),
            'collection'  => $collection,
        ]);
    }

}
