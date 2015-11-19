<?php

use Cake\Routing\Router;

Router::plugin('Unimatrix/Utility', function ($routes) {
    $routes->extensions('json');
    $routes->fallbacks('InflectedRoute');
});
