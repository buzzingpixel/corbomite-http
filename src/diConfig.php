<?php

declare(strict_types=1);

use corbomite\http\ActionParamRouter;
use corbomite\http\ConditionalSapiStreamEmitter;
use corbomite\http\factories\RelayFactory;
use corbomite\http\HttpTwigExtension;
use corbomite\http\Kernel;
use corbomite\http\RequestHelper;
use Grafikart\Csrf\CsrfMiddleware;
use Middlewares\RequestHandler;
use Middlewares\Whoops;
use Psr\Container\ContainerInterface;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run as WhoopsRun;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\ServerRequestFactory;
use Zend\HttpHandlerRunner\Emitter\EmitterStack;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

return [
    Kernel::class => static function (ContainerInterface $di) {
        return new Kernel(
            $di,
            getenv('DEV_MODE') === 'true'
        );
    },
    WhoopsRun::class => static function () {
        return new WhoopsRun();
    },
    PrettyPageHandler::class => static function () {
        return new PrettyPageHandler();
    },
    CsrfMiddleware::class => static function () {
        return new CsrfMiddleware($_SESSION, 200);
    },
    Whoops::class => static function (ContainerInterface $di) {
        return new Whoops($di->get('CorbomiteHttp.WhoopsRunConfigured'));
    },
    'CorbomiteHttp.WhoopsRunConfigured' => static function (ContainerInterface $di) {
        $whoops = $di->get(WhoopsRun::class);

        $whoops->pushHandler(
            $di->get(PrettyPageHandler::class)
        );

        $whoops->register();

        return $whoops;
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
    SapiStreamEmitter::class => static function () {
        return new SapiStreamEmitter();
    },
    EmitterStack::class => static function (ContainerInterface $di) {
        $stack = new EmitterStack();
        $stack->push($di->get(SapiEmitter::class));
        $stack->push($di->get(ConditionalSapiStreamEmitter::class));

        return $stack;
    },
];
