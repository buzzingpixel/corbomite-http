<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2019 BuzzingPixel, LLC
 * @license Apache-2.0
 */

/** @var \FastRoute\RouteCollector $routeCollector */

$routeCollector->get('/test', function () {
    return 'thingy';
});
