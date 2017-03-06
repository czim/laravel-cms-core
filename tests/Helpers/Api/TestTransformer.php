<?php
namespace Czim\CmsCore\Test\Helpers\Api;

use League\Fractal\TransformerAbstract;

class TestTransformer extends TransformerAbstract
{

    /**
     * @param mixed $content
     * @return mixed
     */
    public function transform($content)
    {
        return $content;
    }

}
