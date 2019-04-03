<?php

declare(strict_types=1);

namespace corbomite\http;

use corbomite\configcollector\Collector;
use corbomite\http\factories\RelayFactory;
use FastRoute\RouteCollector;
use Grafikart\Csrf\CsrfMiddleware;
use Middlewares\RequestHandler;
use Middlewares\Whoops;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Diactoros\ServerRequest;
use Zend\HttpHandlerRunner\Emitter\EmitterStack;
use const E_ALL;
use const PHP_URL_PATH;
use function error_reporting;
use function explode;
use function FastRoute\simpleDispatcher;
use function in_array;
use function ini_set;
use function is_array;
use function is_string;
use function ltrim;
use function parse_url;
use function session_start;
use function trim;

class Kernel
{
    /** @var ContainerInterface */
    private $di;
    /** @var bool */
    private $devMode;

    public function __construct(ContainerInterface $di, bool $devMode = false)
    {
        $this->di      = $di;
        $this->devMode = $devMode;
    }

    /**
     * @param string|MiddlewareInterface|array $arg1
     * - If argument is string, must be a class that
     *   implements MiddlewareInterface for error handling when not in dev mode.
     *   Will attempt to get from DI
     * - If argument is an instance of MiddlewareInterface, it will be added to
     *   the middleware stack as an error handler if not in dev mode.
     * - If argument is array, it must be strings or instances of
     *   MiddlewareInterface to add to the middleware stack. If this argument is
     *   an array, the second argument will be ignored
     * @param array                            $arg2
     * Array of class names that implement MiddlewareInterface or instance of
     * MiddlewareInterface
     */
    public function __invoke($arg1 = null, ?array $arg2 = null) : void
    {
        $noDevErrorHandler  = $arg1;
        $incomingMiddleware = [];

        if (is_array($arg1)) {
            $noDevErrorHandler  = null;
            $incomingMiddleware = $arg1;
        } elseif (is_array($arg2)) {
            $noDevErrorHandler  = $arg1;
            $incomingMiddleware = $arg2;
        }

        $collector = $this->di->get(Collector::class);

        $config = $collector->getExtraKeyAsArray('corbomiteHttpConfig');

        $serverRequest = $this->di->get(ServerRequest::class);

        // If we're in dev mode, load up error reporting
        if ($this->devMode) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
            /** @noinspection PhpUnhandledExceptionInspection */
            $middlewareQueue[] = $this->di->get(Whoops::class);
        }

        // If we're not in dev mode, we'll want to capture all the errors
        if (! $this->devMode && $noDevErrorHandler) {
            $added = false;

            if ($noDevErrorHandler instanceof MiddlewareInterface) {
                $middlewareQueue[] = $noDevErrorHandler;
                $added             = true;
            }

            if (! $added) {
                $class = null;

                if ($this->di->has($noDevErrorHandler)) {
                    $class = $this->di->get($noDevErrorHandler);
                }

                if (! $class) {
                    $class = new $noDevErrorHandler();
                }

                $middlewareQueue[] = $class;
            }
        }

        $disableCsrfCheck   = $config['disableCsrfMiddleware'] ?? false;
        $disableCsrfDevMode = $config['disableCsrfMiddlewareDevMode'] ?? false;
        $disableCsrf        = $disableCsrfCheck === true ||
            $this->devMode && $disableCsrfDevMode === true;

        if (! $disableCsrf) {
            $uri         = trim(ltrim($serverRequest->getUri()->getPath(), '/'), '/');
            $uri         = parse_url($uri, PHP_URL_PATH) ?: '';
            $uriSegments = explode('/', is_string($uri) ? $uri : '');

            // Ignore these starting URI segments for CsrfChecking
            $csrfExempt = $config['csrfExemptSegments'] ?? [];

            if (! in_array($uriSegments[0], $csrfExempt, true)) {
                @session_start();
                /** @noinspection PhpUnhandledExceptionInspection */
                $middlewareQueue[] = $this->di->get(CsrfMiddleware::class);
            }
        }

        foreach ($incomingMiddleware as $middleware) {
            if ($middleware instanceof MiddlewareInterface) {
                $middlewareQueue[] = $middleware;
                continue;
            }

            $class = null;

            if ($this->di->has($middleware)) {
                $class = $this->di->get($middleware);
            }

            if (! $class) {
                $class = new $middleware();
            }

            $middlewareQueue[] = $class;
        }

        $disableActionParams = $config['disableActionParamMiddleware'] ?? false;
        $disableActionParams = $disableActionParams === true;

        if (! $disableActionParams) {
            $middlewareQueue[] = $this->di->get(ActionParamRouter::class);
        }

        $middlewareQueue[] = new RouteProcessor(simpleDispatcher(
            static function (RouteCollector $routeCollector) use ($collector) : void {
                $r     = $routeCollector;
                $paths = $collector->getPathsFromExtraKey(
                    'httpRouteConfigFilePath'
                );
                foreach ($paths as $path) {
                    require $path;
                }
            }
        ));

        $middlewareQueue[] = $this->di->get(RequestHandler::class);

        $this->di->get(EmitterStack::class)->emit(
            $this->di->get(RelayFactory::class)
                ->make($middlewareQueue)
                ->handle($serverRequest)
        );
    }
}
