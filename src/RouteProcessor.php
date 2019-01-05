<?php
declare(strict_types=1);

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

namespace corbomite\http;

use FastRoute\Dispatcher;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use corbomite\http\exceptions\Http404Exception;
use corbomite\http\exceptions\Http500Exception;
use Middlewares\Utils\Traits\HasResponseFactory;

class RouteProcessor implements MiddlewareInterface
{
    use HasResponseFactory;

    private $router;
    private $attribute = 'request-handler';

    public function __construct(Dispatcher $router)
    {
        $this->router = $router;
    }

    public function attribute(string $attribute): self
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * @throws Http404Exception
     * @throws Http500Exception
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $route = $this->router->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath()
        );

        if ($route[0] === Dispatcher::NOT_FOUND) {
            throw new Http404Exception('Route not found');
        }

        if ($route[0] === Dispatcher::METHOD_NOT_ALLOWED) {
            throw new Http500Exception('Method not allowed');
        }

        foreach ($route[2] as $name => $value) {
            $request = $request->withAttribute($name, $value);
        }

        $request = $this->setHandler($request, $route[1]);

        return $handler->handle($request);
    }

    protected function setHandler(
        ServerRequestInterface $request,
        $handler
    ): ServerRequestInterface {
        return $request->withAttribute($this->attribute, $handler);
    }
}
