<?php

declare(strict_types=1);

namespace corbomite\tests\RouteProcessor;

use corbomite\http\RouteProcessor;
use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use Throwable;

class AttributeTest extends TestCase
{
    /**
     * @throws Throwable
     */
    public function testAttribute() : void
    {
        /** @noinspection PhpParamsInspection */
        $obj = new RouteProcessor(self::createMock(Dispatcher::class));

        self::assertInstanceOf(
            RouteProcessor::class,
            $obj->attribute('test-attr')
        );
    }
}
