<?php
namespace Czim\CmsCore\Api\Response;

use Czim\CmsCore\Contracts\Api\Response\ResponseBuilderInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Exception;

class RestResponseBuilder implements ResponseBuilderInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }


    /**
     * Returns API-formatted response based on given content.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function response($content, $statusCode = 200)
    {
        return response()
            ->json($this->convertContent($content))
            ->setStatusCode($statusCode);
    }

    /**
     * Returns API-formatted error response.
     *
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    public function error($content, $statusCode = 500)
    {
        return $this->makeErrorResponse($content, $statusCode);
    }

    /**
     * @param mixed $content
     * @return mixed
     */
    protected function convertContent($content)
    {
        return $content;
    }

    /**
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    protected function makeErrorResponse($content, $statusCode)
    {
        if ($content instanceof Exception) {

            if (property_exists($content, 'httpStatusCode')) {
                $statusCode = $content->httpStatusCode;
            }

            $content = $this->formatExceptionError($content);
        }

        return response()
            ->json($content)
            ->setStatusCode($statusCode);
    }

    /**
     * Formats an exception as a standardized error response.
     *
     * @param Exception $e
     * @return mixed
     */
    protected function formatExceptionError(Exception $e)
    {
        if (app()->isLocal() && $this->core->apiConfig('debug.local-exception-trace', true)) {
            return $this->formatExceptionErrorForLocal($e);
        }

        return [
            'message' => $e->getMessage(),
        ];
    }

    /**
     * @param Exception $e
     * @return mixed
     */
    protected function formatExceptionErrorForLocal(Exception $e)
    {
        return [
            'message' => $e->getMessage(),
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => array_map(function($trace) {
                return trim(
                    (isset($trace['file']) ? $trace['file'] . ' : ' . $trace['line'] : '')
                    . '   '
                    . (isset($trace['function']) ? $trace['function']
                        . (isset($trace['class']) ? ' on ' . $trace['class'] : '')
                    : '')
                );
            }, $e->getTrace()),
        ];
    }

}
