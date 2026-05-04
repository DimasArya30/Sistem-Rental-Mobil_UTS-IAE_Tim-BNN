<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
 $routes->group('api', ['namespace' => 'App\Controllers'], function($routes) {
    $routes->group('cars', function($routes) {
        $routes->get('/', 'CarController::index');
        $routes->get('available', 'CarController::index'); // sederhanakan
        $routes->get('(:num)', 'CarController::show/$1');
        $routes->post('/', 'CarController::create');
        $routes->delete('(:num)', 'CarController::delete/$1');
        $routes->put('(:num)/status', 'CarController::updateStatus/$1');
        $routes->get('(:num)/renters', 'CarController::getCarRenters/$1');
    });
});