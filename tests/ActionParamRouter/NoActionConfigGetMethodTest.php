<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use corbomite\http\ActionParamRouter;
use corbomite\configcollector\Collector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class NoActionConfigGetMethodTest extends TestCase
{
    public function test()
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

        $obj = new ActionParamRouter($di);

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getServerParams')
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
            ]);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn([
                'action' => 'asdf',
            ]);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        self::expectException(Exception::class);

        $obj->process($requestMock, $handlerMock);
    }
}
