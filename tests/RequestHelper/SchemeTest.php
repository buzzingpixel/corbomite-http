<?php
declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use PHPUnit\Framework\TestCase;
use corbomite\http\RequestHelper;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class SchemeTest extends TestCase
{
    public function test()
    {
        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $uriMock->expects(self::once())
            ->method('getScheme')
            ->willReturn('schemeVal');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::exactly(2))
            ->method('getUri')
            ->willReturn($uriMock);

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('schemeVal', $requestHelper->scheme());
    }
}
