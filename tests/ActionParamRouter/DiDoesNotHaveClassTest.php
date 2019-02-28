<?php
declare(strict_types=1);

namespace corbomite\tests\ActionParamRouter;

use Zend\Diactoros\Response;
use PHPUnit\Framework\TestCase;
use corbomite\http\ActionParamRouter;
use Psr\Container\ContainerInterface;
use corbomite\configcollector\Collector;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DiDoesNotHaveClassTest extends TestCase
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
            ->willReturn([
                'REQUEST_METHOD' => 'GET',
            ]);

        $requestMock->expects(self::once())
            ->method('getQueryParams')
            ->willReturn([
                'action' => 'testAction',
            ]);

        $handlerMock = self::createMock(RequestHandlerInterface::class);

        $obj = new ActionParamRouter($di);

        $objResponse = $obj->process($requestMock, $handlerMock);

        self::assertInstanceOf(Response::class, $objResponse);

        self::assertEquals(
            598,
            $objResponse->getStatusCode()
        );
    }
}
