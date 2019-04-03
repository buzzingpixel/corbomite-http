<?php

declare(strict_types=1);

namespace corbomite\tests\Kernel;

use corbomite\configcollector\Collector;
use corbomite\http\ActionParamRouter;
use corbomite\http\ConditionalSapiStreamEmitter;
use corbomite\http\factories\RelayFactory;
use corbomite\http\Kernel;
use Exception;
use Grafikart\Csrf\CsrfMiddleware;
use Middlewares\RequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UriInterface;
use Relay\Relay;
use Throwable;
use Zend\Diactoros\ServerRequest;
use Zend\HttpHandlerRunner\Emitter\EmitterStack;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;

class IncomingMiddlewareSecondArgTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test() : void
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
            ->willReturn([TESTS_BASE_PATH . '/Kernel/RequireFile.php']);

        $di = self::createMock(ContainerInterface::class);

        $di->method('has')
            ->willReturnCallback(static function ($key) {
                return $key === MiddlewareClass::class;
            });

        $self = $this;

        $di->method('get')
            ->willReturnCallback(
                static function (string $class) use (
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
                        case MiddlewareClass::class:
                            return new MiddlewareClass();
                        case Collector::class:
                            return $collector;
                        case EmitterStack::class:
                            return $self->createMock(EmitterStack::class);
                        case ConditionalSapiStreamEmitter::class:
                            return $self->createMock(
                                ConditionalSapiStreamEmitter::class
                            );
                        default:
                            throw new Exception('Unknown class');
                    }
                }
            );

        /** @noinspection PhpParamsInspection */
        $kernel = new Kernel($di);

        $kernel->__invoke(
            MiddlewareClass::class,
            [
                MiddlewareClass::class,
                new MiddlewareClass(),
            ]
        );

        self::assertIsBool($_SERVER['REQUIRE_FILE']);
        self::assertTrue($_SERVER['REQUIRE_FILE']);
    }
}
