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

class NoActionConfigGetMethodTest extends TestCase
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
            ->willReturn([]);

        $di = self::createMock(ContainerInterface::class);

        $di->expects(self::once())
            ->method('get')
            ->with(
                self::equalTo(Collector::class)
            )
            ->willReturn($collectorMock);

        /** @noinspection PhpParamsInspection */
        $obj = new ActionParamRouter($di);

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getServerParams')
            ->willReturn(['REQUEST_METHOD' => 'GET']);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn(['action' => 'asdf']);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        self::expectException(Throwable::class);

        /** @noinspection PhpParamsInspection */
        $obj->process($requestMock, $handlerMock);
    }
}
