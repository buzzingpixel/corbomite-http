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
use Composer\Console\Application;
use Zend\Diactoros\ServerRequest;
use Grafikart\Csrf\CsrfMiddleware;
use src\app\http\ActionParamRouter;
use Whoops\Handler\PrettyPageHandler;
use Zend\Diactoros\ServerRequestFactory;
use corbomite\http\ActionConfigCollector;
use corbomite\http\factories\RelayFactory;
use corbomite\http\RouteConfigFileCollector;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

return [
    'corbomiteHttpComposerPackages' => function () {
        // Edge case and weirdness with composer
        getenv('HOME') || putenv('HOME=' . __DIR__);

        $oldCwd = getcwd();

        chdir(APP_BASE_PATH);

        $composerApp = new Application();

        /** @noinspection PhpUnhandledExceptionInspection */
        $composer = $composerApp->getComposer();
        $repositoryManager = $composer->getRepositoryManager();
        $installedFilesystemRepository = $repositoryManager->getLocalRepository();
        $packages = $installedFilesystemRepository->getCanonicalPackages();

        chdir($oldCwd);

        return $packages;
    },
    Kernel::class => function () {
        return new Kernel(new Di());
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
    ActionConfigCollector::class => function () {
        return new ActionConfigCollector(
            Di::get('corbomiteHttpComposerPackages')
        );
    },
    RouteConfigFileCollector::class => function () {
        return new RouteConfigFileCollector(
            Di::get('corbomiteHttpComposerPackages')
        );
    },
    ActionParamRouter::class => function () {
        return new ActionParamRouter(
            Di::get(ActionConfigCollector::class)(),
            new Di()
        );
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
];
