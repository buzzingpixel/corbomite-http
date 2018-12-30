<?php

/** @var \FastRoute\RouteCollector $routeCollector */

$routeCollector->get('/test', function () {
    return 'thingy';
});
