<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http\factories;

use Relay\Relay;

class RelayFactory
{
    public function make(array $middlewareQueue = []): Relay
    {
        return new Relay($middlewareQueue);
    }
}
