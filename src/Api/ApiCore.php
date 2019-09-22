<?php
namespace Czim\CmsCore\Api;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Api\Response\ResponseBuilderInterface;

class ApiCore implements ApiCoreInterface
{

    /**
     * @var ResponseBuilderInterface
     */
    protected $builder;

    /**
     * @param ResponseBuilderInterface $builder
     */
    public function __construct(ResponseBuilderInterface $builder)
    {
        $this->builder = $builder;
    }


    /**
     * Returns API-formatted response based on given content.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function response($content, int $statusCode = 200)
    {
        return $this->builder->response($content, $statusCode);
    }

    /**
     * Returns API-formatted error response.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function error($content, int $statusCode = 500)
    {
        return $this->builder->error($content, $statusCode);
    }

}
