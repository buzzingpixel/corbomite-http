<?php
declare(strict_types=1);

namespace corbomite\tests;

use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use corbomite\http\RouteProcessor;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use corbomite\http\exceptions\Http404Exception;

class ProcessDispatcherNotFoundTest extends TestCase
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

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $dispatcher = self::createMock(Dispatcher::class);

        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::equalTo('getMethodReturnString'),
                self::equalTo('pathString')
            )
            ->willReturn([
                Dispatcher::NOT_FOUND,
            ]);

        self::expectException(Http404Exception::class);

        $obj = new RouteProcessor($dispatcher);

        $obj->process(
            $requestMock,
            $handlerMock
        );
    }
}
