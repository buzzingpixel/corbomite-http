<?php

declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use corbomite\http\RequestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class GetTest extends TestCase
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

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestMock->expects(self::exactly(1))
            ->method('getQueryParams')
            ->willReturn(['asdf' => 'thing']);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('thing', $requestHelper->get('asdf'));

        // *********************************************************************

        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestMock->expects(self::exactly(1))
            ->method('getQueryParams')
            ->willReturn(['asdf' => 'thing']);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertNull($requestHelper->get('stuff'));

        // *********************************************************************

        $uriMock = self::createMock(UriInterface::class);

        $uriMock->expects(self::once())
            ->method('getPath')
            ->willReturn('');

        $requestMock = self::createMock(ServerRequestInterface::class);

        $requestMock->expects(self::once())
            ->method('getUri')
            ->willReturn($uriMock);

        $requestMock->expects(self::exactly(1))
            ->method('getQueryParams')
            ->willReturn(['asdf' => 'thing']);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals(
            'fallbackVal',
            $requestHelper->get('stuff', 'fallbackVal')
        );
    }
}
