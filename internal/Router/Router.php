<?php

declare(strict_types=1);

namespace Internal\Router;

use Internal\Http\Request;
use Internal\Http\Response;
use ReflectionClass;
use Doctrine\Common\Annotations\AnnotationReader;

class Router
{
    protected array $routes = [];

    public function addRoute(object $controller, string $prefix = "/"): void
    {
        $reflection = new ReflectionClass($controller);
        
        // Class-level (attributes or annotations)
        $classMiddleware = $this->getClassMiddleware($reflection);

        foreach ($reflection->getMethods() as $method) {
            // Route & middleware from attributes (PHP 8+)
            $routeAttributes = $method->getAttributes(Route::class);
            $methodMiddleware = $method->getAttributes(RouteMiddleware::class);

            // Route & middleware from annotations (for PHP < 8)
            $methodDocComment = $method->getDocComment();
            $methodMiddlewareAnnotations = [];
            if($methodDocComment){
                $routeAnnotations = $this->parseRouteAnnotation($methodDocComment);
                $methodMiddlewareAnnotations = $this->parseMiddlewareAnnotation($methodDocComment);
            }
            
            // Merge class-level middleware with method-level middleware
            $middlewares = array_merge($classMiddleware, $this->parseAttributesOrAnnotations($methodMiddleware, $methodMiddlewareAnnotations));

            // Register routes
            foreach ($routeAttributes as $attribute) {
                $route = $attribute->newInstance();
                $path = preg_replace('/\/{2,}/',"/",$prefix.'/'.$route->path);
                $this->routes[$route->method][$path] = [
                    'controller' => $controller,
                    'method' => $method->getName(),
                    'middlewares' => $middlewares
                ];
            }
        }
    }

    protected function getClassMiddleware(ReflectionClass $class): array
    {
        $classMiddleware = [];

        // Handle attributes: PHP 8+
        $attributes = $class->getAttributes(RouteMiddleware::class);
        if ($attributes) {
            foreach ($attributes as $attr) {
                $classMiddleware[] = $attr->newInstance()->middlewares;
            }
        }

        // Handle annotations (lower PHP versions)
        $docComment = $class->getDocComment();
        if($docComment){
            $classMiddleware = array_merge($classMiddleware, $this->parseMiddlewareAnnotation($docComment));
        }

        return $classMiddleware;
    }

    public function handle(Request $request, Response $response): void
    {
        $method = $request->getMethod();
        $path = $request->getPath();

        if (isset($this->routes[$method][$path])) {
            $route = $this->routes[$method][$path];

            // Apply middlewares
            $this->applyMiddleware($route['middlewares'], $request, $response);

            // Call controller method
            call_user_func([$route['controller'], $route['method']], $request, $response);
        } else {
            $response->setStatusCode(404)->send('404 Not Found');
        }
    }

    protected function applyMiddleware(array $middlewares, Request $request, Response $response): void
    {
        foreach ($middlewares as $middlewareClass) {
            $middleware = new $middlewareClass();
            $middleware->handle($request, $response, function () {});
        }
    }

    // Parsing annotations
    protected function parseMiddlewareAnnotation(?string $docComment): array
    {
        preg_match_all('/@RouteMiddleware\((.*)\)/', $docComment, $matches);
        return $matches[1] ? explode(',', $matches[1][0]) : [];
    }

    protected function parseRouteAnnotation(?string $docComment): array
    {
        preg_match_all('/@Route\("(.*)", "(.*)"\)/', $docComment, $matches);
        $routes = [];
        foreach ($matches[0] as $index => $match) {
            $routes[] = ['method' => $matches[1][$index], 'path' => $matches[2][$index]];
        }
        return $routes;
    }

    protected function parseAttributesOrAnnotations(array $attributes, array $annotations): array
    {
        $middlewares = [];
        foreach ($attributes as $attr) {
            $middlewares[] = $attr->newInstance()->middlewares;
        }
        return array_merge($middlewares, $annotations);
    }
}
