<?php
declare(strict_types=1);

/**
 * This file is for testing purposes only
 */

use corbomite\di\Di;
use corbomite\http\Kernel;

putenv('DEV_MODE=true');

define('APP_BASE_PATH', dirname(__DIR__));
define('CSRF_EXEMPT_SEGMENTS', 'thing');

require_once dirname(__DIR__) . '/vendor/autoload.php';

Di::get(Kernel::class)();
