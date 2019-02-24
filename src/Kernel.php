<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use corbomite\di\Di;
use Whoops\Run as WhoopsRun;
use corbomite\di\DiException;
use FastRoute\RouteCollector;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use Grafikart\Csrf\CsrfMiddleware;
use Whoops\Handler\PrettyPageHandler;
use function FastRoute\simpleDispatcher;
use corbomite\configcollector\Collector;
use corbomite\http\factories\RelayFactory;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class Kernel
{
    private $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    /**
     * @param string $errorPageClass Called (404, 500) if not in dev mode
     * @throws DiException
     */
    public function __invoke(?string $errorPageClass = null): void
    {
        session_start();

        $devMode = getenv('DEV_MODE') === 'true';

        // If we're in dev mode, load up error reporting
        if ($devMode) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
            /** @noinspection PhpUnhandledExceptionInspection */
            $whoops = $this->di->makeFromDefinition(WhoopsRun::class);
            $whoops->pushHandler(
                $this->di->makeFromDefinition(PrettyPageHandler::class)
            );
            $whoops->register();
            $middlewareQueue[] = $this->di->makeFromDefinition(
                WhoopsMiddleware::class
            );
        }

        // If we're not in dev mode, we'll want to capture all the errors
        if (! $devMode && $errorPageClass) {
            $class = null;

            if ($this->di->hasDefinition($errorPageClass)) {
                $class = $this->di->makeFromDefinition($errorPageClass);
            }

            if (! $class) {
                $class = new $errorPageClass();
            }

            $middlewareQueue[] = $class;
        }

        $uri = trim(ltrim($_SERVER['REQUEST_URI'], '/'), '/');
        $uri = parse_url($uri, PHP_URL_PATH) ?: '';
        $uriSegments = explode('/', \is_string($uri) ? $uri : '');

        // Ignore these starting URI segments for CsrfChecking
        defined('CSRF_EXEMPT_SEGMENTS') || define('CSRF_EXEMPT_SEGMENTS', []);
        $csrfExempt = \is_array(CSRF_EXEMPT_SEGMENTS) ?
            CSRF_EXEMPT_SEGMENTS :
            [CSRF_EXEMPT_SEGMENTS];

        if (! in_array($uriSegments[0], $csrfExempt, true)) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $middlewareQueue[] = $this->di->makeFromDefinition(
                CsrfMiddleware::class
            );
        }

        $middlewareQueue[] = $this->di->makeFromDefinition(
            ActionParamRouter::class
        );

        $collector = $this->di->getFromDefinition(Collector::class);

        $middlewareQueue[] = new RouteProcessor(simpleDispatcher(
            function (RouteCollector $routeCollector) use ($collector) {
                $paths = $collector->getPathsFromExtraKey(
                    'httpRouteConfigFilePath'
                );
                foreach ($paths as $path) {
                    require $path;
                }
            }
        ));

        $middlewareQueue[] = $this->di->makeFromDefinition(
            RequestHandler::class
        );

        $this->di->makeFromDefinition(SapiEmitter::class)->emit(
            $this->di->makeFromDefinition(RelayFactory::class)
                ->make($middlewareQueue)
                ->handle(
                    $this->di->makeFromDefinition(ServerRequest::class)
                )
        );
    }
}
