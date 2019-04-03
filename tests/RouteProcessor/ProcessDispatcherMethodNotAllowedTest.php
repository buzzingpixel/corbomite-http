<?php

declare(strict_types=1);

namespace corbomite\tests;

use corbomite\http\exceptions\Http500Exception;
use corbomite\http\RouteProcessor;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ProcessDispatcherMethodNotAllowedTest extends TestCase
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

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $dispatcher = self::createMock(Dispatcher::class);

        $dispatcher->expects(self::once())
            ->method('dispatch')
            ->with(
                self::equalTo('getMethodReturnString'),
                self::equalTo('pathString')
            )
            ->willReturn([
                Dispatcher::METHOD_NOT_ALLOWED,
            ]);

        self::expectException(Http500Exception::class);

        /** @noinspection PhpParamsInspection */
        $obj = new RouteProcessor($dispatcher);

        /** @noinspection PhpParamsInspection */
        $obj->process(
            $requestMock,
            $handlerMock
        );
    }
}
