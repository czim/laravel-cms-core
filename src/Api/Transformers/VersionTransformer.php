<?php
namespace Czim\CmsCore\Api\Transformers;

use League\Fractal\TransformerAbstract;

class VersionTransformer extends TransformerAbstract
{

    /**
     * @param array $versions
     * @return array
     */
    public function transform(array $versions)
    {
        return $versions;
    }

}
