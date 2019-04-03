<?php

declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use corbomite\configcollector\Collector;
use corbomite\http\ActionParamRouter;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class ClassResponseIsNullTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test() : void
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
            ->willReturn(['REQUEST_METHOD' => 'GET']);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn(['action' => 'testAction']);

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
            ->willReturnCallback(static function ($key) use (
                $collectorMock,
                $callableClassMockMock
            ) {
                switch ($key) {
                    case Collector::class:
                        return $collectorMock;
                    case CallableClassMock::class:
                        return $callableClassMockMock;
                    default:
                        throw new Exception('Unknown Class');
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

        /** @noinspection PhpParamsInspection */
        $obj = new ActionParamRouter($di);

        /** @noinspection PhpParamsInspection */
        $objResponse = $obj->process($requestMock, $handlerMock);

        self::assertEquals($newResponseMock, $objResponse);

        self::assertEquals(5678, $objResponse->getStatusCode());
    }
}
