<?php
namespace Czim\CmsCore\Exceptions;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Exception;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use League\OAuth2\Server\Exception\OAuthException;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Class Handler
 *
 * This CMS handler extends the base laravel handler, so it does not
 * use the app's logic.
 */
class Handler extends ExceptionHandler
{

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->mergeConfiguredDontReportExceptions();
    }

    /**
     * Merges CMS-configured exception classes not to report
     */
    protected function mergeConfiguredDontReportExceptions()
    {
        $this->dontReport = array_merge(
            $this->dontReport,
            config('cms-core.exceptions.dont-report', [])
        );
    }

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param Exception $e
     */
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            cms()->log($e);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Exception  $e
     * @return \Illuminate\Http\Response|Response
     */
    public function render($request, Exception $e)
    {
        if ($core = $this->getCoreIfBound()) {

            if ($core->bootChecker()->isCmsWebRequest()) {
                return $this->renderCmsWebException($request, $e);
            } elseif ($core->bootChecker()->isCmsApiRequest()) {
                return $this->renderCmsApiException($request, $e);
            }
        }

        return parent::render($request, $e);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Exception                $exception
     * @return mixed
     */
    protected function renderCmsWebException($request, Exception $exception)
    {
        if ($request->wantsJson()) {
            return $this->renderJsonException($exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param Exception $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function renderJsonException(Exception $exception)
    {
        $statusCode = $this->getStatusCodeFromException($exception);
        $message    = $exception->getMessage();

        if ($exception instanceof HttpResponseException) {
            $message = $exception->getResponse()->getStatusCode();
        }

        return response()
            ->json(
                [
                    'code'    => $statusCode,
                    'message' => $message,
                ],
                $statusCode
            );
    }

    /**
     * Returns the status code for a given exception.
     *
     * @param Exception $exception
     * @return int
     */
    protected function getStatusCodeFromException(Exception $exception)
    {
        if ($exception instanceof OAuthException) {
            return $exception->httpStatusCode;
        }

        if ($exception instanceof HttpResponseException) {
            return $exception->getResponse()->getStatusCode();
        }

        if ($exception instanceof ValidationException) {
            return 422;
        }

        if (method_exists($exception, 'getStatusCode')) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param Exception                $e
     * @return mixed
     */
    protected function renderCmsApiException($request, Exception $e)
    {
        $core = $this->getCoreIfBound();

        return $core->api()->error($e);
    }

    /**
     * {@inheritdoc}
     */
    protected function convertExceptionToResponse(Exception $e)
    {
        if ($this->isCmsWebRequest() && view()->exists("cms::errors.500")) {
            $flatException = FlattenException::create($e);

            return response()->view("cms::errors.500", [ 'exception' => $flatException ], 500);
        }

        return parent::convertExceptionToResponse($e);
    }

    /**
     * {@inheritdoc}
     *
     * @param HttpException $e
     */
    protected function renderHttpException(HttpException $e)
    {
        $status = $e->getStatusCode();

        if ($this->isCmsWebRequest() &&  view()->exists("cms::errors.{$status}")) {
            $flatException = FlattenException::create($e);

            return response()
                ->view(
                    "cms::errors.{$status}",
                    [ 'exception' => $flatException ],
                    $status,
                    $e->getHeaders()
                );
        }

        return parent::renderHttpException($e);
    }

    /**
     * Returns whether the current request is a CMS web request.
     *
     * @return bool
     */
    protected function isCmsWebRequest()
    {
        $core = $this->getCoreIfBound();

        return $core && $core->bootChecker()->isCmsWebRequest();
    }

    /**
     * @return CoreInterface|null
     */
    protected function getCoreIfBound()
    {
        if ( ! app()->bound(Component::CORE)) {
            return null;
        }

        return app(Component::CORE);
    }
}
