<?php

declare(strict_types=1);

namespace Internal\Middleware;

use Internal\Http\Request;
use Internal\Http\Response;

class LogRequestMiddleware
{
    public function handle(Request $request, Response $response, callable $next): void
    {
        error_log("Request received: " . $request->getPath());
        $next();  // Call the next middleware or controller action
    }
}