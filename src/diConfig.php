<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

use corbomite\http\Kernel;
use Whoops\Run as WhoopsRun;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use corbomite\http\RequestHelper;
use Grafikart\Csrf\CsrfMiddleware;
use corbomite\http\ActionParamRouter;
use corbomite\http\HttpTwigExtension;
use Whoops\Handler\PrettyPageHandler;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\ServerRequestFactory;
use corbomite\http\factories\RelayFactory;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use corbomite\http\ConditionalSapiStreamEmitter;
use Zend\HttpHandlerRunner\Emitter\EmitterStack;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

return [
    Kernel::class => static function (ContainerInterface $di) {
        return new Kernel(
            $di,
            getenv('DEV_MODE') === 'true'
        );
    },
    WhoopsRun::class => function () {
        return new WhoopsRun();
    },
    PrettyPageHandler::class => static function () {
        return new PrettyPageHandler();
    },
    CsrfMiddleware::class => static function () {
        return new CsrfMiddleware($_SESSION, 200);
    },
    WhoopsMiddleware::class => static function () {
        return new WhoopsMiddleware();
    },
    ActionParamRouter::class => static function (ContainerInterface $di) {
        return new ActionParamRouter($di);
    },
    RequestHandler::class => static function (ContainerInterface $di) {
        return new RequestHandler($di);
    },
    SapiEmitter::class => static function () {
        return new SapiEmitter();
    },
    RelayFactory::class => static function () {
        return new RelayFactory();
    },
    ServerRequest::class => static function () {
        return ServerRequestFactory::fromGlobals();
    },
    HttpTwigExtension::class => static function (ContainerInterface $di) {
        return new HttpTwigExtension(
            $di->get(CsrfMiddleware::class),
            $di->get(RequestHelper::class)
        );
    },
    RequestHelper::class => static function (ContainerInterface $di) {
        return new RequestHelper($di->get(ServerRequest::class));
    },
    ConditionalSapiStreamEmitter::class => static function (ContainerInterface $di) {
        return new ConditionalSapiStreamEmitter(
            $di->get(SapiStreamEmitter::class),
            8192
        );
    },
    EmitterStack::class => function () {
        return new EmitterStack();
    },
    SapiStreamEmitter::class => function () {
        return new SapiStreamEmitter();
    },
];
