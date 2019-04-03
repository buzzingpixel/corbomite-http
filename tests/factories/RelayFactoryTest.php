<?php

declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use corbomite\http\factories\RelayFactory;
use PHPUnit\Framework\TestCase;
use Relay\Relay;

class RelayFactoryTest extends TestCase
{
    public function test() : void
    {
        self::assertInstanceOf(
            Relay::class,
            (new RelayFactory())->make()
        );
    }
}
