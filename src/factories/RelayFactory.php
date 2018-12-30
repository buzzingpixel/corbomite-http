<?php
declare(strict_types=1);

namespace corbomite\http\factories;

use Relay\Relay;

class RelayFactory
{
    public function make(array $middlewareQueue = []): Relay
    {
        return new Relay($middlewareQueue);
    }
}
