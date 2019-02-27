<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use Whoops\Run as WhoopsRun;
use FastRoute\RouteCollector;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use Grafikart\Csrf\CsrfMiddleware;
use Whoops\Handler\PrettyPageHandler;
use Psr\Container\ContainerInterface;
use function FastRoute\simpleDispatcher;
use Psr\Http\Server\MiddlewareInterface;
use corbomite\configcollector\Collector;
use corbomite\http\factories\RelayFactory;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class Kernel
{
    private $di;
    private $devMode;

    public function __construct(ContainerInterface $di, bool $devMode = false)
    {
        $this->di = $di;
        $this->devMode = $devMode;
    }

    /**
     * @param string|MiddlewareInterface|array $arg1
     *        - If argument is string, must be a class that
     *          implements MiddlewareInterface for error handling when not in
     *          dev mode. Will attempt to get from DI
     *        - If argument is an instance of MiddlewareInterface, it will be
     *          added to the middleware stack as an error handler if not in
     *          dev mode.
     *        - If argument is array, it must be strings or instances of
     *          MiddlewareInterface to add to the middleware stack. If this
     *          argument is an array, the second argument will be ignored
     * @param array $arg2 Array of class names that implement MiddlewareInterface
     *              or instance of MiddlewareInterface
     */
    public function __invoke($arg1 = null, array $arg2 = null): void
    {
        $noDevErrorHandler = $arg1;
        $incomingMiddleware = [];

        if (\is_array($arg1)) {
            $noDevErrorHandler = null;
            $incomingMiddleware = $arg1;
        } elseif (\is_array($arg2)) {
            $noDevErrorHandler = $arg1;
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
            $whoops = $this->di->get(WhoopsRun::class);
            $whoops->pushHandler(
                $this->di->get(PrettyPageHandler::class)
            );
            $whoops->register();
            $middlewareQueue[] = $this->di->get(WhoopsMiddleware::class);
        }

        // If we're not in dev mode, we'll want to capture all the errors
        if (! $this->devMode && $noDevErrorHandler) {
            $added = false;

            if ($noDevErrorHandler instanceof MiddlewareInterface) {
                $middlewareQueue[] = $noDevErrorHandler;
                $added = true;
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

        $disableCsrf = $config['disableCsrfMiddleware'] ?? false;
        $disableCsrf = $disableCsrf === true;

        if (! $disableCsrf) {
            $uri = trim(ltrim($serverRequest->getUri()->getPath(), '/'), '/');
            $uri = parse_url($uri, PHP_URL_PATH) ?: '';
            $uriSegments = explode('/', \is_string($uri) ? $uri : '');

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
            function (RouteCollector $routeCollector) use ($collector) {
                $r = $routeCollector;
                $paths = $collector->getPathsFromExtraKey(
                    'httpRouteConfigFilePath'
                );
                foreach ($paths as $path) {
                    require $path;
                }
            }
        ));

        $middlewareQueue[] = $this->di->get(RequestHandler::class);

        $this->di->get(SapiEmitter::class)->emit(
            $this->di->get(RelayFactory::class)
                ->make($middlewareQueue)
                ->handle($serverRequest)
        );
    }
}
