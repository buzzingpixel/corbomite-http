<?php

declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use corbomite\http\RequestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class AuthorityTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test() : void
    {
        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $uriMock->expects(self::once())
            ->method('getAuthority')
            ->willReturn('authorityVal');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::exactly(2))
            ->method('getUri')
            ->willReturn($uriMock);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('authorityVal', $requestHelper->authority());
    }
}
