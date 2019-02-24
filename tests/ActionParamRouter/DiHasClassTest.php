<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use corbomite\di\Di;
use PHPUnit\Framework\TestCase;
use corbomite\http\ActionParamRouter;
use Psr\Http\Message\ResponseInterface;
use corbomite\configcollector\Collector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DiHasClassTest extends TestCase
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
                    'method' => 'callableMethod',
                ],
            ]);

        $di = self::createMock(Di::class);

        $di->expects(self::once())
            ->method('getFromDefinition')
            ->with(
                self::equalTo(Collector::class)
            )
            ->willReturn($collectorMock);

        $di->expects(self::exactly(1))
            ->method('hasDefinition')
            ->with(
                self::equalTo(CallableClassMock::class)
            )
            ->willReturn(true);

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

        $altResponse = self::createMock(ResponseInterface::class);

        $altResponse->method('getStatusCode')
            ->willReturn(1234);

        $callableClassMockMock = self::createMock(CallableClassMock::class);

        $callableClassMockMock->expects(self::once())
            ->method('callableMethod')
            ->with(
                self::equalTo($requestMock)
            )
            ->willReturn($altResponse);

        $di->expects(self::once())
            ->method('makeFromDefinition')
            ->with(
                self::equalTo(CallableClassMock::class)
            )
            ->willReturn($callableClassMockMock);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $obj = new ActionParamRouter($di);

        $objResponse = $obj->process($requestMock, $handlerMock);

        self::assertEquals($altResponse, $objResponse);

        self::assertEquals(1234, $objResponse->getStatusCode());
    }
}
