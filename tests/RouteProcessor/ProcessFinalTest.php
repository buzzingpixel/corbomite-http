<?php

declare(strict_types=1);

namespace corbomite\tests;

use corbomite\http\RouteProcessor;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ProcessFinalTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testProcess() : void
    {
        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('pathString');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getMethod')
            ->willReturn('getMethodReturnString');

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestMock->expects(self::exactly(2))
            ->method('withAttribute')
            ->willReturn($requestMock);

        $responseMock = self::createMock(ResponseInterface::class);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with(self::equalTo($requestMock))
            ->willReturn($responseMock);

        $dispatcher = self::createMock(Dispatcher::class);

        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::equalTo('getMethodReturnString'),
                self::equalTo('pathString')
            )
            ->willReturn([
                '',
                '',
                ['attrName' => 'attrVal'],
            ]);

        /** @noinspection PhpParamsInspection */
        $obj = new RouteProcessor($dispatcher);

        /** @noinspection PhpParamsInspection */
        self::assertEquals($responseMock, $obj->process(
            $requestMock,
            $handlerMock
        ));
    }
}
