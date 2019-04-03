<?php

declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use corbomite\configcollector\Collector;
use corbomite\http\ActionParamRouter;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Zend\Diactoros\Response;

class DiDoesNotHaveClassTest extends TestCase
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

        $di = self::createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(
                self::equalTo(Collector::class)
            )
            ->willReturn($collectorMock);

        $di->expects(self::exactly(1))
            ->method('has')
            ->with(
                self::equalTo(CallableClassMock::class)
            )
            ->willReturn(false);

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getServerParams')
            ->willReturn(['REQUEST_METHOD' => 'GET']);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn(['action' => 'testAction']);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        /** @noinspection PhpParamsInspection */
        $obj = new ActionParamRouter($di);

        /** @noinspection PhpParamsInspection */
        $objResponse = $obj->process($requestMock, $handlerMock);

        self::assertInstanceOf(Response::class, $objResponse);

        self::assertEquals(
            598,
            $objResponse->getStatusCode()
        );
    }
}
