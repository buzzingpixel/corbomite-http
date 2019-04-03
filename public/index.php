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

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/devMode.php';

Di::get(Kernel::class)();
