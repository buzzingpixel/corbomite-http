<?php
declare(strict_types=1);

namespace corbomite\tests\Kernel;

use Relay\Relay;
use corbomite\di\Di;
use Whoops\Run as WhoopsRun;
use PHPUnit\Framework\TestCase;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use Grafikart\Csrf\CsrfMiddleware;
use Whoops\Handler\PrettyPageHandler;
use corbomite\http\ActionParamRouter;
use corbomite\configcollector\Collector;
use corbomite\http\factories\RelayFactory;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

use corbomite\http\Kernel;

class DevModeTest extends TestCase
{
    public function test()
    {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(0);

        $_SERVER['REQUEST_URI'] = '';
        putenv('DEV_MODE=false');
        putenv('DEV_MODE=true');

        $actionParamRouter = self::createMock(ActionParamRouter::class);

        $requestHandler = self::createMock(RequestHandler::class);

        $emitter = self::createMock(SapiEmitter::class);

        $requestHandlerFromRelay = self::createMock(Relay::class);

        $relayFactory = self::createMock(RelayFactory::class);

        $relayFactory->expects(self::once())
            ->method('make')
            ->willReturn($requestHandlerFromRelay);

        $serverRequest = self::createMock(ServerRequest::class);

        $csrfMiddleware = self::createMock(CsrfMiddleware::class);

        $collector = self::createMock(Collector::class);

        $collector->expects(self::once())
            ->method('getPathsFromExtraKey')
            ->with(
                self::equalTo('httpRouteConfigFilePath')
            )
            ->willReturn([
                TESTS_BASE_PATH . '/Kernel/RequireFile.php'
            ]);

        $di = self::createMock(Di::class);

        $di->expects(self::once())
            ->method('getFromDefinition')
            ->with(
                self::equalTo(Collector::class)
            )
            ->willReturn($collector);

        $di->method('makeFromDefinition')
            ->willReturnCallback(
                function (string $class) use (
                    $actionParamRouter,
                    $requestHandler,
                    $emitter,
                    $relayFactory,
                    $csrfMiddleware,
                    $serverRequest
                ) {
                    switch ($class) {
                        case ActionParamRouter::class:
                            return $actionParamRouter;
                        case RequestHandler::class:
                            return $requestHandler;
                        case SapiEmitter::class:
                            return $emitter;
                        case RelayFactory::class:
                            return $relayFactory;
                        case CsrfMiddleware::class:
                            return $csrfMiddleware;
                        case ServerRequest::class:
                            return $serverRequest;
                        case KernelErrorClass::class:
                            return new KernelErrorClass();
                        case WhoopsRun::class:
                            return new WhoopsRun();
                        case PrettyPageHandler::class:
                            return new PrettyPageHandler();
                        case WhoopsMiddleware::class:
                            return new WhoopsMiddleware();
                        default:
                            throw new \Exception('Unknown class');
                    }
                }
            );

        $kernel = new Kernel($di);

        $kernel->__invoke(KernelErrorClass::class);

        self::assertIsBool($_SERVER['REQUIRE_FILE']);
        self::assertTrue($_SERVER['REQUIRE_FILE']);

        self::assertEquals('1', ini_get('display_errors'));
        self::assertEquals('1', ini_get('display_startup_errors'));
        self::assertEquals(E_ALL, error_reporting());
    }
}
