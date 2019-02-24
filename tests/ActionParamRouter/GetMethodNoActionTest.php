<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use corbomite\di\Di;
use PHPUnit\Framework\TestCase;
use corbomite\http\ActionParamRouter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetMethodNoActionTest extends TestCase
{
    public function test()
    {
        $di = self::createMock(Di::class);

        $obj = new ActionParamRouter($di);

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
            ]);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn([]);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $responseMock = self::createMock(ResponseInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->willReturn($responseMock);

        self::assertEquals(
            $responseMock,
            $obj->process($requestMock, $handlerMock)
        );
    }
}
