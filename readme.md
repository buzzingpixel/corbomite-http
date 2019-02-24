# Corbomite HTTP

<p><a href="https://travis-ci.org/buzzingpixel/corbomite-http"><img src="https://api.travis-ci.org/buzzingpixel/corbomite-http.svg?branch=master"></a></p>

Part of BuzzingPixel's Corbomite project.

Provides a light framework for responding to HTTP requests.

## Usage

In your HTTP front controller, use the dependency injector to call the Kernel (note that `APP_BASE_PATH` must be defined).

```php
<?php
declare(strict_types=1);

// phpcs:ignoreFile

use corbomite\di\Di;
use corbomite\http\Kernel as HttpKernel;

define('APP_BASE_PATH', __DIR__);
define('APP_VENDOR_PATH', APP_BASE_PATH . '/vendor');

require APP_VENDOR_PATH . '/autoload.php';

Di::get(HttpKernel::class)();
```

## CSRF

Corbomite HTTP implements [CSRF PSR-15 Middleware](https://github.com/Grafikart/PSR15-CsrfMiddleware) to prevent cross-site forgery on post requests. However, sometimes it may be desirable for certain uri starting segments not to implement CSRF protection. In those cases, you can define a constant before the Kernel is run to define the segment or segments that should not implement CSRF protection:

```php
define('CSRF_EXEMPT_SEGMENTS', 'my-segment');

// or

define('CSRF_EXEMPT_SEGMENTS', [
    'my-segment',
    'another-segment',
]);
```

## Dev mode

The Kernel looks for an environment variable named `DEV_MODE`. If that is set to a string of `'true'` the Kernel will attempt to crank up PHP error reporting, and will register [Whoops](https://github.com/filp/whoops) for error reporting.

## Error page class

When not in dev mode, you can send a fully qualified class name as an argument to the Kernel's `__invoke()` method. This class will be added to the Middleware queue so you could implement a try/catch to handle errors. That class must implement the `\Psr\Http\Server\MiddlewareInterface`. Here's an example of such a class:

```php
<?php
declare(strict_types=1);

namespace src\app\http;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use corbomite\http\exceptions\Http404Exception;
use some\name\space\RenderErrorPageAction;

class ErrorPages implements MiddlewareInterface
{
    private $renderErrorPage;

    public function __construct(RenderErrorPageAction $renderErrorPage)
    {
        $this->renderErrorPage = $renderErrorPage;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            $code = 500;

            if ($e instanceof Http404Exception ||
                $e->getPrevious() instanceof Http404Exception
            ) {
                $code = 404;
            }

            return ($this->renderErrorPage)($code);
        }
    }
}
```

This allows you to render custom error pages.

The Kernel will attempt to get the specified class from the [Corbomite Dependency Injector](https://github.com/buzzingpixel/corbomite-di) and fall back to `new`ing up the class.

## Routing

Corbomite HTTP uses [FastRoute](https://github.com/nikic/FastRoute) for routing. Your app or any composer package can register routes by providing a `httpRouteConfigFilePath` in the `extra` composer.json object.

```json
{
    "name": "vendor/name",
    "extra": {
        "httpRouteConfigFilePath": "src/routes.php"
    }
}
```

The called route file will have a variable available by the name of `$routeCollector`. Here's an example route file:

```php
<?php
declare(strict_types=1);

/** @var \FastRoute\RouteCollector $routeCollector */

$routeCollector->get(
    '/my/route',
    \someclass\implementing\MiddlewareInterface::class // Must have __invoke() method
);

$routeCollector->get('/another/route', \someclass\Thing::class);
```

## ActionParams

Action params are available as either query params `/some-uri?action=myAction` on `get` requests or as post body params on `post` request `action=someAction`.

Actions are processed before routes and are great for handling, say, post requests and if the post request does not validate, then the route will continue to process. If the post request does validate, you could redirect to a new page or return a response of some kind.

In order to request an action, action config will need to be set up. Your app or any composer package can defined an action param config file in the `extra` object of composer.json with the `httpActionConfigFilePath` key:

```json
{
    "name": "vendor/name",
    "extra": {
        "httpActionConfigFilePath": "src/actionConfig.php"
    }
}
```

The action config file should return an array of actions like so:

```php
<?php
declare(strict_types=1);

return [
    'myAction' => [
        'class' => \some\name\space\MyClass::class,
        'method' => 'myMethod', // Or defaults to __invoke()
    ],
    'anotherAction' => [
        'class' => \some\name\space\AnotherClass::class
    ],
];
```

## Twig Extension

Corbomite HTTP provides a [Twig](https://twig.symfony.com/) extension. If you're using [Corbomite Twig](https://github.com/buzzingpixel/corbomite-twig) this extension will be loaded automatically. Otherwise, you can add it to your own Twig instance Twig's `addExtension()` method like this:

```php
<?php
declare(strict_types=1);

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use corbomite\http\HttpTwigExtension;

$twig = new Environment(new FilesystemLoader('/path/to/templates'), [
    'debug' => true,
    'cache' => '/path/to/cache',
    'strict_variables' => true,
]);

$twig->addExtension(new HttpTwigExtension());
```

### `{{ throwHttpError() }}`

This twig function is for throwing an HTTP error from Twig. The default is to throw the 404 exception. Pass 500 in as an argument throw a 500 internal server error.

### `{{ getCsrfFormKey() }}`

Gets the form key name for the CSRF token.

### `{{ generateCsrfToken() }}`

Generates and outputs a CSRF token.

### `{{ getCsrfInput() }}`

Outputs a hidden input for the CSRF token.

## License

Copyright 2018 BuzzingPixel, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at [http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0).

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
