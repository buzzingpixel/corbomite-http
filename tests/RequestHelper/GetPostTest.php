<?php
declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use PHPUnit\Framework\TestCase;
use corbomite\http\RequestHelper;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class GetPostTest extends TestCase
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

        $requestMock->expects(self::exactly(0))
            ->method('getQueryParams')
            ->willReturn([
                'test1' => 'thing1',
            ]);

        $requestMock->expects(self::exactly(1))
            ->method('getParsedBody')
            ->willReturn([
                'test2' => 'thing2',
            ]);

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('thing2', $requestHelper->getPost('test2'));

        /**********************************************************************/

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
            ->willReturn([
                'test1' => 'thing1',
            ]);

        $requestMock->expects(self::exactly(1))
            ->method('getParsedBody')
            ->willReturn([
                'test2' => 'thing2',
            ]);

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals('thing1', $requestHelper->getPost('test1'));

        /**********************************************************************/

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
            ->willReturn([
                'test1' => 'thing1',
            ]);

        $requestMock->expects(self::exactly(1))
            ->method('getParsedBody')
            ->willReturn([
                'test2' => 'thing2',
            ]);

        $requestHelper = new RequestHelper($requestMock);

        self::assertNull($requestHelper->getPost('asdf'));

        /**********************************************************************/

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
            ->willReturn([
                'test1' => 'thing1',
            ]);

        $requestMock->expects(self::exactly(1))
            ->method('getParsedBody')
            ->willReturn([
                'test2' => 'thing2',
            ]);

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals(
            'fallbackVal',
            $requestHelper->getPost('stuff', 'fallbackVal')
        );
    }
}
