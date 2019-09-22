<?php
namespace Czim\CmsCore\Api\Response;

use Czim\CmsCore\Contracts\Api\Response\ResponseBuilderInterface;
use Czim\CmsCore\Contracts\Api\Response\TransformContainerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Exceptions\HttpResponseException;
use League\Fractal\Manager as FractalManager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item as FractalItem;
use League\Fractal\Serializer\DataArraySerializer;
use League\Fractal\TransformerAbstract;
use UnexpectedValueException;

class RestResponseBuilder implements ResponseBuilderInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var FractalManager
     */
    protected $fractalManager;

    /**
     * FQN for the Fractal data serializer to use
     *
     * @var string
     */
    protected $fractalSerializer = DataArraySerializer::class;


    public function __construct(CoreInterface $core, FractalManager $manager)
    {
        $this->core           = $core;
        $this->fractalManager = $manager;

        $this->initializeFractal();
    }

    /**
     * Prepares Fractal manager for processing.
     *
     * @return $this
     */
    protected function initializeFractal(): ResponseBuilderInterface
    {
        $this->fractalManager->setSerializer(app($this->fractalSerializer));

        return $this;
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
    public function error($content, int $statusCode = 500)
    {
        return $this->makeErrorResponse($content, $statusCode);
    }

    /**
     * Converts raw content to the desired output format.
     *
     * @param mixed $content
     * @return mixed
     */
    protected function convertContent($content)
    {
        if ($content instanceof TransformContainerInterface) {
            return $this->transformContent($content);
        }

        return $content;
    }

    /**
     * Transforms content wrapped in a transform container.
     *
     * @param TransformContainerInterface $container
     * @return mixed
     */
    protected function transformContent(TransformContainerInterface $container)
    {
        if (null === $container->getTransformer()) {
            return $container->getContent();
        }

        $transformer = $this->makeTransformerInstance($container->getTransformer());
        $content     = $container->getContent();

        if ($container->isCollection()) {

            // Detect pagination and convert apply it for Fractal
            $paginator   = null;

            if ($content instanceof Paginator) {
                $paginator = $content;
                $content   = $content->items();
            }

            /** @var FractalCollection $resource */
            $resource = new FractalCollection($content, $transformer);

            if ($paginator) {
                $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
            }

        } else {
            /** @var FractalItem $resource */
            $resource = new FractalItem($content, $transformer);
        }

        return $this->fractalManager->createData($resource)->toArray();
    }

    /**
     * Makes an instance of a fractal transformer.
     *
     * @param string|TransformerAbstract $transformerClass
     * @return TransformerAbstract
     */
    protected function makeTransformerInstance($transformerClass): TransformerAbstract
    {
        if ($transformerClass instanceof TransformerAbstract) {
            return $transformerClass;
        }

        $instance = app($transformerClass);

        if ( ! ($instance instanceof TransformerAbstract)) {
            throw new UnexpectedValueException("{$transformerClass} is not a TransformerAbstract");
        }

        return $instance;
    }

    /**
     * @param mixed $content
     * @param int   $statusCode
     * @return mixed
     */
    protected function makeErrorResponse($content, int $statusCode)
    {
        $data = null;

        if ($content instanceof Exception) {

            if ($content instanceof HttpResponseException) {
                $statusCode = $content->getResponse()->getStatusCode();
                $data       = json_decode($content->getResponse()->getContent(), true);
            } elseif (method_exists($content, 'getStatusCode')) {
                $statusCode = $content->getStatusCode();
            } elseif (property_exists($content, 'httpStatusCode')) {
                $statusCode = $content->httpStatusCode;
            }

            $content = $this->formatExceptionError($content, $this->getStandardMessageForStatusCode($statusCode));
        }

        return response()
            ->json($this->normalizeErrorResponse($content, $data))
            ->setStatusCode($statusCode);
    }

    /**
     * Normalizes error response to array.
     *
     * @param mixed       $content
     * @param null|array  $data     extra data to provide in the error message
     * @return array
     */
    protected function normalizeErrorResponse($content, ?array $data = null): array
    {
        if ($content instanceof Arrayable) {
            $content = $content->toArray();
        }

        if ( ! is_array($content)) {
            $content = [
                'message' => $content,
            ];
        }

        if (null !== $data) {
            $content['data'] = $data;
        }

        return $content;
    }

    /**
     * Formats an exception as a standardized error response.
     *
     * @param Exception   $e
     * @param null|string $fallbackMessage  message to use if exception has none
     * @return mixed
     */
    protected function formatExceptionError(Exception $e, ?string $fallbackMessage = null)
    {
        if (    (config('app.debug') || $this->core->config('debug'))
            &&  $this->core->apiConfig('debug.local-exception-trace', true)
        ) {
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
    protected function formatExceptionErrorForLocal(Exception $e, ?string $fallbackMessage = null)
    {
        $message = $e->getMessage() ?: $fallbackMessage;

        return [
            'message' => $message,
            'code'    => $e->getCode(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
            'trace'   => array_map(static function ($trace) {
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

    /**
     * Returns standard message to use for a given HTTP status code.
     *
     * @param int $code
     * @return null|string
     */
    protected function getStandardMessageForStatusCode(int $code): ?string
    {
        switch ($code) {

            case 422:
                return 'Unprocessable Entity';

            case 404:
                return 'Not Found';

            case 403:
                return 'Forbidden';

            case 401:
                return 'Unauthorized';

            case 400:
                return 'Bad Request';

            case 500:
                return 'Server Whoops';

            default:
                // @codeCoverageIgnoreStart
                return null;
                // @codeCoverageIgnoreEnd
        }
    }

}
