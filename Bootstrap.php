<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Internal\Router\Router;
use Internal\Http\Request;
use Internal\Http\Response;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


// Initialize Router
$router = new Router();

// Add controllers
//API
$router->addRoute(new App\Controllers\API\IndexController());
//WEB
$router->addRoute(new App\Controllers\WEB\MainController());

// Add Global Middlewares
$globalMiddlewares = [
    Internal\Middleware\LogRequestMiddleware::class,
    Internal\Middleware\CorsMiddleware::class,
];

// Handle Request
$request = new Request();
$response = new Response();

foreach ($globalMiddlewares as $middlewareClass) {
    $middleware = new $middlewareClass();
    $middleware->handle($request, $response, fn() => null); // Global middleware
}



// Route handling and response middleware
$router->handle($request, $response);
