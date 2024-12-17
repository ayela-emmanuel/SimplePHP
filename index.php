<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Internal\Router\Router;
use Internal\Http\Request;
use Internal\Http\Response;

try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (\Throwable $th) {
    echo "Failed to load ENV: See .env.example For Sample.";
    die();
}



// Initialize Router
const ROUTER = new Router();

// Add controllers


//API
ROUTER->addRoute(new App\Controllers\API\IndexController(),"api");
//WEB
ROUTER->addRoute(new App\Controllers\WEB\MainController());

// Add Global Middlewares
$globalMiddlewares = [
    Internal\Middleware\LogRequestMiddleware::class,
    Internal\Middleware\CorsMiddleware::class
];


//Internal
//ENABLE_DEBUG
if($_ENV["ENABLE_DEBUG"]?? false){
    ROUTER->addRoute(new Internal\Controllers\DebuggingController(),"/debug");
}


// Handle Request
$request = new Request();
$response = new Response();

foreach ($globalMiddlewares as $middlewareClass) {
    $middleware = new $middlewareClass();
    $middleware->handle($request, $response, fn() => null); // Global middleware
}

ROUTER->handle($request, $response);
