<?php

namespace Internal\Middleware;

interface Middleware
{
    public function handle(\Closure $next);
}