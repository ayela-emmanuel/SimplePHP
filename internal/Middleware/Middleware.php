<?php

namespace Internal\Middleware;
use Internal\Http\Request;
use Internal\Http\Response;

interface Middleware
{
    public function handle(Request $request, Response $response, callable $next):void;
}