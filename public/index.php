<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

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
