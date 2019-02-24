<?php
declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use PHPUnit\Framework\TestCase;
use corbomite\http\RequestHelper;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class AttributesTest extends TestCase
{
    public function test()
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

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals($arrayReturn, $requestHelper->attributes());
    }
}
