<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use Psr\Http\Message\ServerRequestInterface;

use corbomite\http\interfaces\RequestHelperInterface;

class RequestHelper implements RequestHelperInterface
{
    private $request;

    private $uri;
    private $segments = [];

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;

        $uri = $request->getUri()->getPath();
        $uri = preg_replace('/([^:])(\/{2,})/', '$1/', $uri);
        $uri = ltrim(rtrim($uri, '/'), '/');
        $this->uri = $uri;

        if (! $uri) {
            return;
        }

        $segments = explode('/', $uri);
        array_unshift($segments, '');
        unset($segments[0]);

        $this->segments = $segments;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function segments(): array
    {
        return $this->segments;
    }

    public function segment(int $n, $fallback = null): ?string
    {
        return $this->segments[$n] ?? $fallback;
    }

    public function serverParam($n, $fallback = null)
    {
        return $this->request->getServerParams()[$n] ?? $fallback;
    }

    public function get(string $n, $fallback = null): ?string
    {
        return $this->request->getQueryParams()[$n] ?? $fallback;
    }

    public function post(string $n, $fallback = null)
    {
        return $this->request->getParsedBody()[$n] ?? $fallback;
    }

    public function getPost(string $n, $fallback = null)
    {
        return $this->request->getParsedBody()[$n] ??
            $this->request->getQueryParams()[$n] ??
            $fallback;
    }

    public function attributes(): array
    {
        return $this->request->getAttributes();
    }

    public function attribute(string $n, $fallback = null)
    {
        return $this->request->getAttribute($n, $fallback);
    }

    public function method(): string
    {
        return strtolower($this->request->getMethod());
    }

    public function scheme(): string
    {
        return $this->request->getUri()->getScheme();
    }

    public function authority(): string
    {
        return $this->request->getUri()->getAuthority();
    }

    public function host(): string
    {
        return $this->request->getUri()->getHost();
    }

    public function port(): ?int
    {
        return $this->request->getUri()->getPort();
    }

    public function queryString(): string
    {
        return $this->request->getUri()->getQuery();
    }
}
