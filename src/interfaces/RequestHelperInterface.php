<?php

declare(strict_types=1);

namespace corbomite\http\interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RequestHelperInterface
{
    /**
     * Gets the Server Request
     */
    public function request() : ServerRequestInterface;

    /**
     * Gets the URI with no leading or trailing slashes
     */
    public function uri() : string;

    /**
     * Returns an array of URI segments. Each key in the array should match the
     * segment number. First segment's key should be 1, second 2, etc.
     *
     * @return string[]
     */
    public function segments() : array;

    /**
     * Returns requested segment or $fallback if it is not set/does not exist
     *
     * @param mixed $fallback
     */
    public function segment(int $n, $fallback = null) : ?string;

    /**
     * Returns value of requested server param or $fallback if not set/does
     * not exist
     *
     * @param mixed $n
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function serverParam($n, $fallback = null);

    /**
     * Returns value of specified get param or $fallback if not set/does
     * not exist
     *
     * @param mixed $fallback
     */
    public function get(string $n, $fallback = null) : ?string;

    /**
     * Returns value of specified post param or $fallback if not set/does
     * not exist
     *
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function post(string $n, $fallback = null);

    /**
     * Returns value of post param if exists, get param of post param of name
     * doesn't exist, or $fallback if neither exists
     *
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function getPost(string $n, $fallback = null);

    /**
     * Returns the requests array of attributes
     *
     * @return mixed[]
     */
    public function attributes() : array;

    /**
     * Returns the specified attribute if exists or $fallback if not exists
     *
     * @param mixed $fallback
     *
     * @return mixed
     */
    public function attribute(string $n, $fallback = null);

    /**
     * Returns the request method as ALL LOWER CASE (strtolower)
     */
    public function method() : string;

    /**
     * Returns the request scheme
     */
    public function scheme() : string;

    /**
     * Returns the uri authority component
     */
    public function authority() : string;

    /**
     * Returns the uri host
     */
    public function host() : string;

    /**
     * Returns the uri port
     */
    public function port() : ?int;

    /**
     * Returns the query string
     */
    public function queryString() : string;
}
