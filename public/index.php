<?php

declare(strict_types=1);

use corbomite\di\Di;
use corbomite\http\Kernel;

putenv('DEV_MODE=true');

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/devMode.php';

Di::get(Kernel::class)();
