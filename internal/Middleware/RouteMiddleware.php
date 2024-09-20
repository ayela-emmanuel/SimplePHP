<?php

namespace Internal\Router;

#[\Attribute]
class RouteMiddleware
{
    public array $middlewares;

    public function __construct(...$middlewares)
    {
        $this->middlewares = $middlewares;
    }
}
