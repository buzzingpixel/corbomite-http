<?php
declare(strict_types=1);

namespace corbomite\tests\RequestHelper;

use Relay\Relay;
use PHPUnit\Framework\TestCase;
use corbomite\http\factories\RelayFactory;

class RelayFactoryTest extends TestCase
{
    public function test()
    {
        self::assertInstanceOf(
            Relay::class,
            (new RelayFactory())->make()
        );
    }
}
