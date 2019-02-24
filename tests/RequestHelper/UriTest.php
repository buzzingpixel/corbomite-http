<?php
declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use PHPUnit\Framework\TestCase;
use corbomite\http\RequestHelper;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class UriTest extends TestCase
{
    public function test()
    {
        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('', $requestHelper->uri());

        /**********************************************************************/

        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('test/testing');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('test/testing', $requestHelper->uri());
    }
}
