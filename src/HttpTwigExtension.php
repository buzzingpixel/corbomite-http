<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use Twig_Function;
use Twig_Extension;
use corbomite\http\exceptions\Http404Exception;
use corbomite\http\exceptions\Http500Exception;

class HttpTwigExtension extends Twig_Extension
{
    public function getFunctions(): array
    {
        return [new Twig_Function('throwHttpError', [$this, 'throwHttpError'])];
    }

    /**
     * @throws Http404Exception
     * @throws Http500Exception
     */
    public function throwHttpError(int $code = 404, string $msg = ''): void
    {
        if ($code === 404) {
            throw new Http404Exception($msg);
        }

        throw new Http500Exception($msg);
    }
}
