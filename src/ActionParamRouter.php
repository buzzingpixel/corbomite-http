<?php

declare(strict_types=1);

namespace corbomite\http;

use corbomite\configcollector\Collector;
use Exception;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function class_exists;
use function is_string;
use function method_exists;
use function strtolower;

class ActionParamRouter implements MiddlewareInterface
{
    /** @var ContainerInterface $di */
    private $di;

    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @throws Exception
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ) : ResponseInterface {
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

        $actionConfig = $this->di->get(Collector::class)->collect(
            'httpActionConfigFilePath'
        );

        $actionConfig = $actionConfig[$action] ?? null;

        if (! $actionConfig) {
            throw new Exception('Action config not found');
        }

        if (is_string($actionConfig)) {
            $actionConfig = ['class' => $actionConfig];
        }

        $actionClass  = $actionConfig['class'] ?? '';
        $actionMethod = $actionConfig['method'] ?? '__invoke';

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

        if ($this->di->has($actionClass)) {
            $constructedClass = $this->di->get($actionClass);
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
