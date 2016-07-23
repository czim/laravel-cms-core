<?php
namespace Czim\CmsCore\Contracts\Api;

interface ApiCoreInterface
{

    /**
     * Returns API-formatted response based on given content.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function response($content, $statusCode = 200);

}
