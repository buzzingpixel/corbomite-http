<?php

declare(strict_types=1);

namespace corbomite\http\factories;

use Psr\Http\Server\MiddlewareInterface;
use Relay\Relay;

class RelayFactory
{
    /**
     * @param MiddlewareInterface[] $middlewareQueue
     */
    public function make(array $middlewareQueue = []) : Relay
    {
        return new Relay($middlewareQueue);
    }
}
