<?php
declare(strict_types=1);

namespace corbomite\tests\RouteProcessor;

use FastRoute\Dispatcher;
use PHPUnit\Framework\TestCase;
use corbomite\http\RouteProcessor;

class AttributeTest extends TestCase
{
    public function testAttribute()
    {
        $obj = new RouteProcessor(self::createMock(Dispatcher::class));

        self::assertInstanceOf(
            RouteProcessor::class,
            $obj->attribute('test-attr')
        );
    }
}
