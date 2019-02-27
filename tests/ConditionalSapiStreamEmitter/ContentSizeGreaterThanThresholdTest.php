<?php
declare(strict_types=1);

namespace corbomite\tests\Kernel;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use corbomite\http\ConditionalSapiStreamEmitter;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

class ContentSizeGreaterThanThresholdTest extends TestCase
{
    public function test()
    {
        $body = $this->createMock(StreamInterface::class);

        $body->expects(self::once())
            ->method('getSize')
            ->willReturn(10);

        $response = $this->createMock(ResponseInterface::class);

        $response->expects(self::once())
            ->method('getBody')
            ->willReturn($body);

        $response->expects(self::exactly(0))
            ->method('getHeaderLine');

        $streamEmitter = $this->createMock(SapiStreamEmitter::class);

        $streamEmitter->expects(self::once())
            ->method('emit')
            ->with(self::equalTo($response))
            ->willReturn(true);

        $emitter = new ConditionalSapiStreamEmitter($streamEmitter, 9);

        self::assertTrue($emitter->emit($response));
    }
}