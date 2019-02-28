<?php
declare(strict_types=1);

namespace corbomite\http;

use Psr\Http\Message\ResponseInterface;
use Zend\HttpHandlerRunner\Emitter\EmitterInterface;
use Zend\HttpHandlerRunner\Emitter\SapiStreamEmitter;

/**
 * @see https://framework.zend.com/blog/2017-09-14-diactoros-emitters.html
 */
class ConditionalSapiStreamEmitter implements EmitterInterface
{
    private $streamEmitter;
    private $contentSizeThresholdInBytes;

    public function __construct(
        SapiStreamEmitter $streamEmitter,
        int $contentSizeThresholdInBytes
    ) {
        $this->streamEmitter = $streamEmitter;
        $this->contentSizeThresholdInBytes = $contentSizeThresholdInBytes;
    }

    public function emit(ResponseInterface $response): bool
    {
        $contentSize = $response->getBody()->getSize();

        if ($contentSize < $this->contentSizeThresholdInBytes
            && $response->getHeaderLine('content-range') === ''
        ) {
            return false;
        }

        return $this->streamEmitter->emit($response);
    }
}
