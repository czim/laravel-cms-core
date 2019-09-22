<?php
namespace Czim\CmsCore\Contracts\Api\Response;

interface ResponseBuilderInterface
{

    /**
     * Returns API-formatted response based on given content.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function response($content, int $statusCode = 200);

    /**
     * Returns API-formatted error response.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function error($content, int $statusCode = 500);

}
