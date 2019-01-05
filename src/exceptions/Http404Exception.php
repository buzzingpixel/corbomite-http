<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http\exceptions;

use Exception;
use Throwable;

class Http404Exception extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 404,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
