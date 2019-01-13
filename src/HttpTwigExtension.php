<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use Twig_Markup;
use Twig_Function;
use Twig_Extension;
use Grafikart\Csrf\CsrfMiddleware;
use corbomite\http\exceptions\Http404Exception;
use corbomite\http\exceptions\Http500Exception;

class HttpTwigExtension extends Twig_Extension
{
    private $csrfMiddleware;

    public function __construct(CsrfMiddleware $csrfMiddleware)
    {
        $this->csrfMiddleware = $csrfMiddleware;
    }

    public function getFunctions(): array
    {
        return [
            new Twig_Function('throwHttpError', [$this, 'throwHttpError']),
            new Twig_Function('getCsrfFormKey', [$this, 'getCsrfFormKey']),
            new Twig_Function('generateCsrfToken', [$this, 'generateCsrfToken']),
            new Twig_Function('getCsrfInput', [$this, 'getCsrfInput']),
        ];
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

    public function getCsrfFormKey(): string
    {
        return $this->csrfMiddleware->getFormKey();
    }

    public function generateCsrfToken(): string
    {
        return $this->csrfMiddleware->generateToken();
    }

    public function getCsrfInput(): Twig_Markup
    {
        return new Twig_Markup(
            '<input type="hidden" name="' .
                $this->getCsrfFormKey() .
                '" value="' .
                $this->generateCsrfToken() .
                '">',
            'UTF-8'
        );
    }
}
