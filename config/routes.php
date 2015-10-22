<?php

use Cake\Routing\Router;

Router::plugin('Borg', function ($routes) {
    $routes->extensions('json');
    $routes->fallbacks('InflectedRoute');
});