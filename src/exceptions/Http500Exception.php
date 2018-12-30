<?php
declare(strict_types=1);

namespace corbomite\http\exceptions;

use Exception;
use Throwable;

class Http500Exception extends Exception
{
    public function __construct(
        string $message = '',
        int $code = 500,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
