<?php

namespace Internal\Router;

#[\Attribute]
class Route
{
    public string $method;
    public string $path;

    public function __construct(string $method, string $path)
    {
        $this->method = $method;
        $this->path = $path;
    }
}