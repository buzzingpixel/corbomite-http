<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */
use corbomite\di\Di;
use corbomite\http\Kernel;
use Whoops\Run as WhoopsRun;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use corbomite\http\RequestHelper;
use Grafikart\Csrf\CsrfMiddleware;
use corbomite\http\ActionParamRouter;
use corbomite\http\HttpTwigExtension;
use Whoops\Handler\PrettyPageHandler;
use Zend\Diactoros\ServerRequestFactory;
use corbomite\http\factories\RelayFactory;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

return [
    Kernel::class => function () {
        return new Kernel(
            Di::diContainer(),
            getenv('DEV_MODE') === 'true'
        );
    },
    WhoopsRun::class => function () {
        return new WhoopsRun();
    },
    PrettyPageHandler::class => function () {
        return new PrettyPageHandler();
    },
    CsrfMiddleware::class => function () {
        return new CsrfMiddleware($_SESSION, 200);
    },
    WhoopsMiddleware::class => function () {
        return new WhoopsMiddleware();
    },
    ActionParamRouter::class => function () {
        return new ActionParamRouter(new Di());
    },
    RequestHandler::class => function () {
        return new RequestHandler(Di::diContainer());
    },
    SapiEmitter::class => function () {
        return new SapiEmitter();
    },
    RelayFactory::class => function () {
        return new RelayFactory();
    },
    ServerRequest::class => function () {
        return ServerRequestFactory::fromGlobals();
    },
    HttpTwigExtension::class => function () {
        return new HttpTwigExtension(
            Di::get(CsrfMiddleware::class),
            Di::get(RequestHelper::class)
        );
    },
    RequestHelper::class => function () {
        return new RequestHelper(Di::get(ServerRequest::class));
    },
];
