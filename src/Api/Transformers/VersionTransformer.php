<?php
namespace Czim\CmsCore\Api\Transformers;

use League\Fractal\TransformerAbstract;

class VersionTransformer extends TransformerAbstract
{

    /**
     * @param array $version
     * @return array
     */
    public function transform(array $version)
    {
        return $version;
    }

}
