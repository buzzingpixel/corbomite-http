<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
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

        $di = self::createMock(ContainerInterface::class);

        $di->expects(self::exactly(2))
            ->method('get')
            ->willReturn($collectorMock)
            ->willReturnCallback(function ($key) use (
                $collectorMock,
                $callableClassMockMock
            ) {
                switch ($key) {
                    case Collector::class:
                        return $collectorMock;
                    case CallableClassMock::class:
                        return $callableClassMockMock;
                    default:
                        throw new \Exception('Unknown Class');
                }
            });

        $di->expects(self::exactly(1))
            ->method('has')
            ->with(
                self::equalTo(CallableClassMock::class)
            )
            ->willReturn(true);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $obj = new ActionParamRouter($di);

        $objResponse = $obj->process($requestMock, $handlerMock);

        self::assertEquals($altResponse, $objResponse);

        self::assertEquals(1234, $objResponse->getStatusCode());
    }
}
