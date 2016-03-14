<?php

use Cake\Routing\Router;

Router::plugin('Unimatrix/Utility', function ($routes) {
    $routes->connect('/social/counter', ['controller' => 'Social', 'action' => 'counter']);
});