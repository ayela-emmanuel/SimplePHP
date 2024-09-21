<?php 
declare(strict_types=1);

namespace App\Middleware;

use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Middleware\Middleware;



class DemoMiddleware implements Middleware
{
    
    public function handle(Request $request, Response $response, callable $next): void
    {
        //logic
        $next();  // Call the next middleware or controller action
        //logic after
    }
}


