<?php

declare(strict_types=1);

namespace corbomite\http;

use corbomite\http\exceptions\Http404Exception;
use corbomite\http\exceptions\Http500Exception;
use corbomite\http\interfaces\RequestHelperInterface;
use Grafikart\Csrf\CsrfMiddleware;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class HttpTwigExtension extends AbstractExtension
{
    /** @var CsrfMiddleware */
    private $csrfMiddleware;
    /** @var RequestHelperInterface */
    private $requestHelper;

    public function __construct(
        CsrfMiddleware $csrfMiddleware,
        RequestHelperInterface $requestHelper
    ) {
        $this->csrfMiddleware = $csrfMiddleware;
        $this->requestHelper  = $requestHelper;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions() : array
    {
        return [
            new TwigFunction('throwHttpError', [$this, 'throwHttpError']),
            new TwigFunction('getCsrfFormKey', [$this, 'getCsrfFormKey']),
            new TwigFunction('generateCsrfToken', [$this, 'generateCsrfToken']),
            new TwigFunction('getCsrfInput', [$this, 'getCsrfInput']),
            new TwigFunction('requestHelper', [$this, 'requestHelper']),
        ];
    }

    /**
     * @throws Http404Exception
     * @throws Http500Exception
     */
    public function throwHttpError(int $code = 404, string $msg = '') : void
    {
        if ($code === 404) {
            throw new Http404Exception($msg);
        }

        throw new Http500Exception($msg);
    }

    public function getCsrfFormKey() : string
    {
        return $this->csrfMiddleware->getFormKey();
    }

    public function generateCsrfToken() : string
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this->csrfMiddleware->generateToken();
    }

    public function getCsrfInput() : Markup
    {
        return new Markup(
            '<input type="hidden" name="' .
                $this->getCsrfFormKey() .
                '" value="' .
                $this->generateCsrfToken() .
                '">',
            'UTF-8'
        );
    }

    public function requestHelper() : RequestHelperInterface
    {
        return $this->requestHelper;
    }
}
