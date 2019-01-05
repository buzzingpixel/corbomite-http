<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use Exception;
use corbomite\di\Di;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionParamRouter implements MiddlewareInterface
{
    private $actionConfig;
    private $di;

    public function __construct(array $actionConfig, Di $di)
    {
        $this->actionConfig = $actionConfig;
        $this->di = $di;
    }

    /**
     * @throws Exception
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $requestMethod = strtolower(
            $request->getServerParams()['REQUEST_METHOD'] ?? 'get'
        );

        $action = null;

        if ($requestMethod === 'get') {
            $action = $request->getQueryParams()['action'] ?? null;

            // Pass the request on up the middleware stack
            if (! $action) {
                return $handler->handle($request);
            }
        }

        if ($requestMethod !== 'get') {
            $action = $request->getParsedBody()['action'] ?? null;

            // Pass the request on up the middleware stack
            if (! $action) {
                return $handler->handle($request);
            }
        }

        // Pass the request on up the middleware stack
        if (! $action) {
            return $handler->handle($request);
        }

        $actionClass = $this->actionConfig[$action]['class'] ?? '';
        $actionMethod = $this->actionConfig[$action]['method'] ?? '__invoke';

        if (! $actionClass) {
            throw new Exception('Action class config not found');
        }

        if (! class_exists($actionClass)) {
            throw new Exception('Action class not found');
        }

        if (! method_exists($actionClass, $actionMethod)) {
            throw new Exception(
                'Action method not found: ' . $actionClass . '::' .
                $actionMethod . '()'
            );
        }

        $constructedClass = null;

        if ($this->di->hasDefinition($actionClass)) {
            $constructedClass = $this->di->makeFromDefinition($actionClass);
        }

        if (! $constructedClass) {
            $constructedClass = new $actionClass();
        }

        $response = $constructedClass->{$actionMethod}($request);

        if ($response === null) {
            return $handler->handle($request);
        }

        return $response;
    }
}
