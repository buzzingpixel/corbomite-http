<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use PHPUnit\Framework\TestCase;
use corbomite\http\ActionParamRouter;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use corbomite\configcollector\Collector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ClassResponseIsNullTest extends TestCase
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
            ->willReturn(null);

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

        $newResponseMock = self::createMock(ResponseInterface::class);

        $newResponseMock->method('getStatusCode')
            ->willReturn(5678);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $handlerMock->expects(self::once())
            ->method('handle')
            ->with(
                self::equalTo($requestMock)
            )
            ->willReturn($newResponseMock);

        $obj = new ActionParamRouter($di);

        $objResponse = $obj->process($requestMock, $handlerMock);

        self::assertEquals($newResponseMock, $objResponse);

        self::assertEquals(5678, $objResponse->getStatusCode());
    }
}
