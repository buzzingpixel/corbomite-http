<?php

declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use corbomite\http\RequestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class AttributesTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test() : void
    {
        $arrayReturn = [
            'test' => 'thing',
            'foo' => 'bar',
        ];

        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestMock->expects(self::once())
            ->method('getAttributes')
            ->willReturn($arrayReturn);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals($arrayReturn, $requestHelper->attributes());
    }
}
