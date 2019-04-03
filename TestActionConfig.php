<?php

declare(strict_types=1);

use corbomite\tests\Kernel\MiddlewareClass;

return [
    'asdf' => ['stuff'],
    'testAction' => MiddlewareClass::class,
    'myAction' => ['class' => 'ASDf'],
];
