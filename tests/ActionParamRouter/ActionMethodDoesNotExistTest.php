<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use Exception;
use corbomite\di\Di;
use PHPUnit\Framework\TestCase;
use corbomite\http\ActionParamRouter;
use Psr\Http\Message\ResponseInterface;
use corbomite\configcollector\Collector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionMethodDoesNotExistTest extends TestCase
{
    public function test()
    {
        $collectorMock = self::createMock(Collector::class);

        $collectorMock->expects(self::once())
            ->method('collect')
            ->with(
                self::equalTo('httpActionConfigFilePath')
            )
            ->willReturn([
                'testAction' => [
                    'class' => CallableClassMock::class,
                    'method' => 'Noop',
                ],
            ]);

        $di = self::createMock(Di::class);

        $di->expects(self::once())
            ->method('getFromDefinition')
            ->with(
                self::equalTo(Collector::class)
            )
            ->willReturn($collectorMock);

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
            ]);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn([
                'action' => 'testAction',
            ]);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $responseMock = self::createMock(ResponseInterface::class);

        $obj = new ActionParamRouter($di);

        self::expectException(Exception::class);

        self::assertEquals(
            $responseMock,
            $obj->process($requestMock, $handlerMock)
        );
    }
}
