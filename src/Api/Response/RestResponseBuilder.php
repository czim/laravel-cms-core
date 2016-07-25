<?php
namespace Czim\CmsCore\Api\Response;

use Czim\CmsCore\Contracts\Api\Response\ResponseBuilderInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

            if (method_exists($content, 'getStatusCode')) {
                $statusCode = $content->getStatusCode();
            } elseif (property_exists($content, 'httpStatusCode')) {
                $statusCode = $content->httpStatusCode;
            }

            $content = $this->formatExceptionError($content, $this->getStandardMessageForStatusCode($statusCode));
        }

        return response()
            ->json($this->normalizeErrorResponse($content))
            ->setStatusCode($statusCode);
    }

    /**
     * Normalizes error response to array.
     *
     * @param mixed $content
     * @return array
     */
    protected function normalizeErrorResponse($content)
    {
        if ($content instanceof Arrayable) {
            $content = $content->toArray();
        }

        if (is_array($content)) {
            return $content;
        }

        return [
            'message' => $content,
        ];
    }

    protected function getStandardMessageForStatusCode($code)
    {
        if ( ! $code) {
            return null;
        }

        switch ($code) {

            case 404:
                return 'Not Found';

            case 403:
                return "Forbidden";

            case 401:
                return "Unauthorized";

            case 400:
                return "Bad Request";

            case 500:
                return 'Server Whoops';

            default:
                return null;
        }
    }

    /**
     * Formats an exception as a standardized error response.
     *
     * @param Exception   $e
     * @param null|string $fallbackMessage  message to use if exception has none
     * @return mixed
     */
    protected function formatExceptionError(Exception $e, $fallbackMessage = null)
    {
        if (app()->isLocal() && $this->core->apiConfig('debug.local-exception-trace', true)) {
            return $this->formatExceptionErrorForLocal($e, $fallbackMessage);
        }

        $message = $e->getMessage() ?: $fallbackMessage;

        return [
            'message' => $message,
        ];
    }

    /**
     * Formats an exception as a standardized error response for local environment only.
     *
     * @param Exception   $e
     * @param null|string $fallbackMessage  message to use if exception has none
     * @return mixed
     */
    protected function formatExceptionErrorForLocal(Exception $e, $fallbackMessage = null)
    {
        $message = $e->getMessage() ?: $fallbackMessage;

        return [
            'message' => $message,
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
