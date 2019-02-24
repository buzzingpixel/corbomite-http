<?php
declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use PHPUnit\Framework\TestCase;
use corbomite\http\RequestHelper;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

class AttributeTest extends TestCase
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

        $requestMock->expects(self::once())
            ->method('getAttribute')
            ->with(
                self::equalTo('attrInput'),
                self::equalTo('fallbackVal')
            )
            ->willReturn('attr-return');

        $requestHelper = new RequestHelper($requestMock);

        self::assertEquals(
            'attr-return',
            $requestHelper->attribute('attrInput', 'fallbackVal')
        );
    }
}
