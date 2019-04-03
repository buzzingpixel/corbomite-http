<?php

declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use corbomite\http\ActionParamRouter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class GetMethodNoActionTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test() : void
    {
        $di = self::createMock(ContainerInterface::class);

        /** @noinspection PhpParamsInspection */
        $obj = new ActionParamRouter($di);

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getServerParams')
            ->willReturn(['REQUEST_METHOD' => 'GET']);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn([]);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $responseMock = self::createMock(ResponseInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->willReturn($responseMock);

        /** @noinspection PhpParamsInspection */
        self::assertEquals(
            $responseMock,
            $obj->process($requestMock, $handlerMock)
        );
    }
}
