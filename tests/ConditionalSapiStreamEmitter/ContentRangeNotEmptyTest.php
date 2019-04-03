<?php

declare(strict_types=1);

namespace corbomite\tests\Kernel;

use corbomite\http\ConditionalSapiStreamEmitter;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Throwable;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

class ContentRangeNotEmptyTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function test() : void
    {
        $body = $this->createMock(StreamInterface::class);

        $body->expects(self::once())
            ->method('getSize')
            ->willReturn(8);

        $response = $this->createMock(ResponseInterface::class);

        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $response->expects(self::once())
            ->method('getHeaderLine')
            ->with(self::equalTo('content-range'))
            ->willReturn('asdf');

        $streamEmitter = $this->createMock(SapiStreamEmitter::class);

        $streamEmitter->expects(self::once())
            ->method('emit')
            ->with(self::equalTo($response))
            ->willReturn(true);

        /** @noinspection PhpParamsInspection */
        $emitter = new ConditionalSapiStreamEmitter($streamEmitter, 9);

        /** @noinspection PhpParamsInspection */
        self::assertTrue($emitter->emit($response));
    }
}
