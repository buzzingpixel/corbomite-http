# Corbomite HTTP

<p><a href="https://travis-ci.org/buzzingpixel/corbomite-http"><img src="https://api.travis-ci.org/buzzingpixel/corbomite-http.svg?branch=master"></a></p>

Part of BuzzingPixel's Corbomite project.

Provides a light framework for responding to HTTP requests.

## Usage

In your HTTP front controller, use the dependency injector to call the Kernel (note that `APP_BASE_PATH` can optionally be defined as a constant and must be where your vendor directory resides. Otherwise the path to your vendor directory will be figured out automatically).

```php
<?php
declare(strict_types=1);

use corbomite\di\Di;
use corbomite\http\Kernel as HttpKernel;

require __DIR__ . '/vendor/autoload.php';

Di::get(HttpKernel::class)();
```

### Kernel Method Arguments

The Kernel can receive up to two arguments.

If the first argument is an array, the second argument will be ignored, and the first argument as an array must send class names or class instances as values of the array that implement `\Psr\Http\Server\MiddlewareInterface`. These classes will be added to the middleware stack after any error handlers are added (and after the CSRF middleware if that's not disabled) and before Corbomite HTTP's Action params and routing.

If the first argument is a string or a class, it must implement `\Psr\Http\Server\MiddlewareInterface` and will be added as the error handler if the environment variable `DEV_MODE` is not set to a string of `true`. The second argument then can be an array of middleware as above.

### Error Handling

If the environment variable `DEV_MODE` is set to a string of `true`, the Kernel will attempt to set PHP error output to maximum and add `\Franzl\Middleware\Whoops\WhoopsMiddleware` as the first item to the middleware stack. In this way, as you develop, you can get whatever information and trace you need over your HTTP stack when an error is encountered.

If not in dev mode, and if you've provided a middleware to handle errors as described above, then your provided error middleware will be the first thing added to the middleware stack. In this way, you have a chance to render error pages in your app in production.

If you send a string class name, the Kernel will attempt to get the class from the [Corbomite Dependency Injector](https://github.com/buzzingpixel/corbomite-di) and fall back to `new`ing up the class.

Here's an example of an error handler middleware class:

```php
<?php
declare(strict_types=1);

namespace src\app\http;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use some\name\space\RenderErrorPageAction;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use corbomite\http\exceptions\Http404Exception;

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

### CSRF Protection

By default, Corbomite HTTP adds [CSRF PSR-15 Middleware](https://github.com/Grafikart/PSR15-CsrfMiddleware) to the middleware stack to prevent cross-site forgery on post requests.

Sometimes, it may be desirable to have certain segments that are exempt from CSRF middleware. If you wish to disable the middleware for certain segments, you can define a config for the disabled segments in your composer.json extra object:

```json
{
    "name": "my/app",
    "extra": {
        "corbomiteHttpConfig": {
            "csrfExemptSegments": [
                "my-segment",
                "segment"
            ]
        }
    }
}
```

You can also disable the CSRF middleware altogether in the JSON extra object like so:

```json
{
    "name": "my/app",
    "extra": {
        "corbomiteHttpConfig": {
            "disableCsrfMiddleware": true
        }
    }
}
```

### Routing

Corbomite HTTP uses [FastRoute](https://github.com/nikic/FastRoute) for routing. Your app or any composer package can register routes by providing a `httpRouteConfigFilePath` in the `extra` composer.json object.

```json
{
    "name": "my/app",
    "extra": {
        "httpRouteConfigFilePath": "src/routes.php"
    }
}
```

The called route file will have a variable available by the name of `$routeCollector` (and a shortcut variable of `$r` if you prefer brevity). Here's an example route file:

```php
<?php
declare(strict_types=1);

/** @var \FastRoute\RouteCollector $r */
/** @var \FastRoute\RouteCollector $routeCollector */

$routeCollector->get(
    '/my/route',
    \someclass\implementing\MiddlewareInterface::class // Must have __invoke() method
);

$routeCollector->get('/another/route', \someclass\Thing::class);
```

### ActionParams

Action params are available as either query params `/some-uri?action=myAction` on `get` requests or as post body params on `post` request `action=someAction`.

Actions are processed before routes and are great for handling, say, post requests and if the post request does not validate, then the route will continue to process. If the post request does validate, you could redirect to a new page or return a response of some kind.

In order to request an action, action config will need to be set up. Your app or any composer package can define an action param config file in the `extra` object of composer.json with the `httpActionConfigFilePath` key:

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

#### Disabling Action Params

You can also disable action params altogether in the JSON extra object like so:

```json
{
    "name": "my/app",
    "extra": {
        "corbomiteHttpConfig": {
            "disableActionParamMiddleware": true
        }
    }
}
```

### Twig Extension

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

#### `{{ throwHttpError() }}`

This twig function is for throwing an HTTP error from Twig. The default is to throw the 404 exception. Pass 500 in as an argument throw a 500 internal server error.

#### `{{ getCsrfFormKey() }}`

Gets the form key name for the CSRF token.

#### `{{ generateCsrfToken() }}`

Generates and outputs a CSRF token.

#### `{{ getCsrfInput() }}`

Outputs a hidden input for the CSRF token.

## License

Copyright 2019 BuzzingPixel, LLC

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at [http://www.apache.org/licenses/LICENSE-2.0](http://www.apache.org/licenses/LICENSE-2.0).

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
