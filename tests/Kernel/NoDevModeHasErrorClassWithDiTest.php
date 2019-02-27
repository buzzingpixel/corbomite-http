<?php
declare(strict_types=1);

namespace corbomite\tests\Kernel;

use Relay\Relay;
use corbomite\di\Di;
use PHPUnit\Framework\TestCase;
use Middlewares\RequestHandler;
use Zend\Diactoros\ServerRequest;
use Grafikart\Csrf\CsrfMiddleware;
use Psr\Http\Message\UriInterface;
use corbomite\http\ActionParamRouter;
use corbomite\configcollector\Collector;
use corbomite\http\factories\RelayFactory;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

use corbomite\http\Kernel;

class NoDevModeHasErrorClassWithDiTest extends TestCase
{
    public function test()
    {
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

        $di = self::createMock(Di::class);

        $di->expects(self::once())
            ->method('hasDefinition')
            ->with(self::equalTo(KernelErrorClass::class))
            ->willReturn(true);

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
                        default:
                            throw new \Exception('Unknown class');
                    }
                }
            );

        $kernel = new Kernel($di);

        $kernel->__invoke(KernelErrorClass::class);

        self::assertIsBool($_SERVER['REQUIRE_FILE']);
        self::assertTrue($_SERVER['REQUIRE_FILE']);
    }
}
