<?php

declare(strict_types=1);

namespace corbomite\http;

use corbomite\http\interfaces\RequestHelperInterface;
use Psr\Http\Message\ServerRequestInterface;
use function array_unshift;
use function explode;
use function ltrim;
use function preg_replace;
use function rtrim;
use function strtolower;

class RequestHelper implements RequestHelperInterface
{
    /** @var ServerRequestInterface */
    private $request;

    /** @var string */
    private $uri;
    /** @var string[] */
    private $segments = [];

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;

        $uri       = $request->getUri()->getPath();
        $uri       = preg_replace('/([^:])(\/{2,})/', '$1/', $uri);
        $uri       = ltrim(rtrim($uri, '/'), '/');
        $this->uri = $uri;

        if (! $uri) {
            return;
        }

        $segments = explode('/', $uri);
        array_unshift($segments, '');
        unset($segments[0]);

        $this->segments = $segments;
    }

    public function request() : ServerRequestInterface
    {
        return $this->request;
    }

    public function uri() : string
    {
        return $this->uri;
    }

    /**
     * @return string[]
     */
    public function segments() : array
    {
        return $this->segments;
    }

    /**
     * @param mixed $fallback
     */
    public function segment(int $n, $fallback = null) : ?string
    {
        return $this->segments[$n] ?? $fallback;
    }

    /**
     * @param mixed $n
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function serverParam($n, $fallback = null)
    {
        return $this->request->getServerParams()[$n] ?? $fallback;
    }

    /**
     * @param mixed $fallback
     */
    public function get(string $n, $fallback = null) : ?string
    {
        return $this->request->getQueryParams()[$n] ?? $fallback;
    }

    /**
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function post(string $n, $fallback = null)
    {
        return $this->request->getParsedBody()[$n] ?? $fallback;
    }

    /**
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function getPost(string $n, $fallback = null)
    {
        return $this->request->getParsedBody()[$n] ??
            $this->request->getQueryParams()[$n] ??
            $fallback;
    }

    /**
     * @return mixed[]
     */
    public function attributes() : array
    {
        return $this->request->getAttributes();
    }

    /**
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function attribute(string $n, $fallback = null)
    {
        return $this->request->getAttribute($n, $fallback);
    }

    public function method() : string
    {
        return strtolower($this->request->getMethod());
    }

    public function scheme() : string
    {
        return $this->request->getUri()->getScheme();
    }

    public function authority() : string
    {
        return $this->request->getUri()->getAuthority();
    }

    public function host() : string
    {
        return $this->request->getUri()->getHost();
    }

    public function port() : ?int
    {
        return $this->request->getUri()->getPort();
    }

    public function queryString() : string
    {
        return $this->request->getUri()->getQuery();
    }
}
