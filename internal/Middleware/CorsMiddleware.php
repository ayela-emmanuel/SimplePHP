<?php

declare(strict_types=1);

namespace Internal\Middleware;

use Internal\Http\Request;
use Internal\Http\Response;

class CorsMiddleware
{
    // Allowed origins for CORS requests
    protected array $allowedOrigins = ['*']; // You can specify allowed origins here like 'https://example.com'

    // Allowed HTTP methods
    protected array $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'];

    // Allowed headers
    protected array $allowedHeaders = ['Content-Type', 'Authorization'];

    // Whether credentials (cookies, authorization headers, etc.) are allowed
    protected bool $allowCredentials = false;

    // Max age of the preflight request (in seconds)
    protected int $maxAge = 86400; // 24 hours

    public function handle(Request $request, Response $response, callable $next): void
    {
        // Add CORS headers to the response
        $this->addCorsHeaders($response);

        // If it's a preflight request (OPTIONS), just return without calling the next middleware
        if ($request->getMethod() === 'OPTIONS') {
            $response->setStatusCode(204)->send(''); // No content for preflight response
            return;
        }

        // Continue with the next middleware or the actual request handler
        $next();
    }

    protected function addCorsHeaders(Response $response): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        // Check if the origin is allowed
        if ($this->isOriginAllowed($origin)) {
            $response->setContentType('text/html');

            header('Access-Control-Allow-Origin: ' . $origin);
            header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));

            if ($this->allowCredentials) {
                header('Access-Control-Allow-Credentials: true');
            }

            header('Access-Control-Max-Age: ' . $this->maxAge);
        }
    }

    protected function isOriginAllowed(string $origin): bool
    {
        // Allow all origins if the array contains "*"
        if (in_array('*', $this->allowedOrigins, true)) {
            return true;
        }

        // Otherwise, check if the origin is in the allowed list
        return in_array($origin, $this->allowedOrigins, true);
    }
}
