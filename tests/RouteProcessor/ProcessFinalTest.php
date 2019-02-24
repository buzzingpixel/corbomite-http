<?php
declare(strict_types=1);

namespace corbomite\tests;

use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use corbomite\http\RouteProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ProcessFinalTest extends TestCase
{
    public function testProcess()
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
                [
                    'attrName' => 'attrVal',
                ]
            ]);

        $obj = new RouteProcessor($dispatcher);

        self::assertEquals($responseMock, $obj->process(
            $requestMock,
            $handlerMock
        ));
    }
}
