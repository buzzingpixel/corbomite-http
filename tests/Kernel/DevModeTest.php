<?php
declare(strict_types=1);

namespace corbomite\tests\Kernel;

use Relay\Relay;
use Whoops\Run as WhoopsRun;
use PHPUnit\Framework\TestCase;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use Grafikart\Csrf\CsrfMiddleware;
use Psr\Http\Message\UriInterface;
use Whoops\Handler\PrettyPageHandler;
use corbomite\http\ActionParamRouter;
use Psr\Container\ContainerInterface;
use corbomite\configcollector\Collector;
use corbomite\http\factories\RelayFactory;
use Franzl\Middleware\Whoops\WhoopsMiddleware;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\HttpHandlerRunner\Emitter\EmitterStack;
use corbomite\http\ConditionalSapiStreamEmitter;

use corbomite\http\Kernel;

class DevModeTest extends TestCase
{
    public function test()
    {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(0);

        $actionParamRouter = self::createMock(ActionParamRouter::class);

        $requestHandler = self::createMock(RequestHandler::class);

        $emitter = self::createMock(SapiEmitter::class);

        $requestHandlerFromRelay = self::createMock(Relay::class);

        $relayFactory = self::createMock(RelayFactory::class);

        $relayFactory->expects(self::once())
            ->method('make')
            ->willReturn($requestHandlerFromRelay);

        $uriInterface = self::createMock(UriInterface::class);

        $uriInterface->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $serverRequest = self::createMock(ServerRequest::class);

        $serverRequest->expects(self::once())
            ->method('getUri')
            ->willReturn($uriInterface);

        $csrfMiddleware = self::createMock(CsrfMiddleware::class);

        $collector = self::createMock(Collector::class);

        $collector->expects(self::once())
            ->method('getExtraKeyAsArray')
            ->with(self::equalTo('corbomiteHttpConfig'))
            ->willReturn([]);

        $collector->expects(self::once())
            ->method('getPathsFromExtraKey')
            ->with(
                self::equalTo('httpRouteConfigFilePath')
            )
            ->willReturn([
                TESTS_BASE_PATH . '/Kernel/RequireFile.php'
            ]);

        $di = self::createMock(ContainerInterface::class);

        $self = $this;

        $di->method('get')
            ->willReturnCallback(
                function (string $class) use (
                    $actionParamRouter,
                    $requestHandler,
                    $emitter,
                    $relayFactory,
                    $csrfMiddleware,
                    $serverRequest,
                    $collector,
                    $self
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
                        case Collector::class:
                            return $collector;
                        case EmitterStack::class:
                            return $self->createMock(EmitterStack::class);
                        case ConditionalSapiStreamEmitter::class:
                            return $self->createMock(
                                ConditionalSapiStreamEmitter::class
                            );
                        default:
                            throw new \Exception('Unknown class');
                    }
                }
            );

        $kernel = new Kernel($di, true);

        $kernel->__invoke(KernelErrorClass::class);

        self::assertIsBool($_SERVER['REQUIRE_FILE']);
        self::assertTrue($_SERVER['REQUIRE_FILE']);

        self::assertEquals('1', ini_get('display_errors'));
        self::assertEquals('1', ini_get('display_startup_errors'));
        self::assertEquals(E_ALL, error_reporting());
    }
}
