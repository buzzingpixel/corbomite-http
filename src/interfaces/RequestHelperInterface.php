<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http\interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RequestHelperInterface
{
    /**
     * Gets the Server Request
     * @return ServerRequestInterface
     */
    public function request(): ServerRequestInterface;

    /**
     * Gets the URI with no leading or trailing slashes
     * @return string
     */
    public function uri(): string;

    /**
     * Returns an array of URI segments. Each key in the array should match the
     * segment number. First segment's key should be 1, second 2, etc.
     * @return array
     */
    public function segments(): array;

    /**
     * Returns requested segment or $fallback if it is not set/does not exist
     * @param int $n
     * @param $fallback
     * @return string|null
     */
    public function segment(int $n, $fallback = null): ?string;

    /**
     * Returns value of requested server param or $fallback if not set/does
     * not exist
     * @param $n
     * @param $fallback
     * @return mixed
     */
    public function serverParam($n, $fallback = null);

    /**
     * Returns value of specified get param or $fallback if not set/does
     * not exist
     * @param string $n
     * @param $fallback
     * @return string
     */
    public function get(string $n, $fallback = null): ?string;

    /**
     * Returns value of specified post param or $fallback if not set/does
     * not exist
     * @param string $n
     * @param $fallback
     * @return mixed
     */
    public function post(string $n, $fallback = null);

    /**
     * Returns value of post param if exists, get param of post param of name
     * doesn't exist, or $fallback if neither exists
     * @param string $n
     * @param $fallback
     * @return mixed
     */
    public function getPost(string $n, $fallback = null);

    /**
     * Returns the requests array of attributes
     * @return array
     */
    public function attributes(): array;

    /**
     * Returns the specified attribute if exists or $fallback if not exists
     * @param string $n
     * @param null $fallback
     * @return mixed
     */
    public function attribute(string $n, $fallback = null);

    /**
     * Returns the request method as ALL LOWER CASE (strtolower)
     * @return string
     */
    public function method(): string;

    /**
     * Returns the request scheme
     * @return string
     */
    public function scheme(): string;

    /**
     * Returns the uri authority component
     * @return string
     */
    public function authority(): string;

    /**
     * Returns the uri host
     * @return string
     */
    public function host(): string;

    /**
     * Returns the uri port
     * @return int|null
     */
    public function port(): ?int;

    /**
     * Returns the query string
     * @return string
     */
    public function queryString(): string;
}
