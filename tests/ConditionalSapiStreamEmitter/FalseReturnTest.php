<?php
declare(strict_types=1);

namespace corbomite\tests\Kernel;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use corbomite\http\ConditionalSapiStreamEmitter;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

class FalseReturnTest extends TestCase
{
    public function test()
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
            ->willReturn('');

        $streamEmitter = $this->createMock(SapiStreamEmitter::class);

        $emitter = new ConditionalSapiStreamEmitter($streamEmitter, 9);

        self::assertFalse($emitter->emit($response));
    }
}
