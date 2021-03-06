<?php

declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use corbomite\http\RequestHelper;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Throwable;

class PostTest extends TestCase
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
            ->method('getParsedBody')
            ->willReturn(['asdf' => 'thing']);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('thing', $requestHelper->post('asdf'));

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
            ->method('getParsedBody')
            ->willReturn(['asdf' => 'thing']);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertNull($requestHelper->post('stuff'));

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
            ->method('getParsedBody')
            ->willReturn(['asdf' => 'thing']);

        /** @noinspection PhpParamsInspection */
        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals(
            'fallbackVal',
            $requestHelper->post('stuff', 'fallbackVal')
        );
    }
}
