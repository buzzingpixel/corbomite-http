<?php

declare(strict_types=1);

use FastRoute\RouteCollector;

/** @var RouteCollector $r */
/** @var RouteCollector $routeCollector */

$routeCollector->get('/test', static function () {
    return 'thingy';
});
