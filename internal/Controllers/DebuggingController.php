<?php 

namespace Internal\Controllers;

use Exception;
use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Router\Route;
use Internal\Utils\GlobalLogger;
use Internal\Attributes\AutoDocAttribute;
use Internal\Attributes\AutoDocComment;
use Internal\Attributes\RequestModelAttribute;

class DebuggingController
{
    #[Route("GET","/logs")]
    public function logs(Request $request, Response $response){

        $response->setStatusCode(200);
        $data = array_reverse(explode("```",GlobalLogger::load()));
        
        $response->sendTemplate(__DIR__."/../Templates/logs.html",["data"=>$data],true);
    }
    #[Route("DELETE","/logs/clear")]
    public function clearlogs(Request $request, Response $response){

        $response->setStatusCode(200);
        GlobalLogger::clear();
        //$data = array_reverse(explode("```",GlobalLogger::load()));
        
        //$response->redirect("/dev/logs");
    }

    #[Route("GET","/docs/api")]
    public function api_docs(Request $request, Response $response){

        $response->setStatusCode(200);
        $routes = ROUTER->GetRoutes();

        $filteredRoutes = [];

        // Iterate over route data
        
        
        // Iterate over all routes
        foreach ($routes as $method => $paths) {
            foreach ($paths as $path => $route) {
                $controller = $route['controller'];
                $action = $route['method'];
                
                // Use ReflectionClass to reflect on the controller
                $reflectionClass = new \ReflectionClass($controller);

                // Check if the class has AutoDocAttribute
                $classAttributes = $reflectionClass->getAttributes(AutoDocAttribute::class);
                if (!empty($classAttributes)) {
                    // Default comment at class level
                    $comment = "";
                    $requestModelStructure = null;

                    // Check for AutoDocComment at class level
                    $commentAttributes = $reflectionClass->getAttributes(AutoDocComment::class);
                    if (!empty($commentAttributes)) {
                        $comment = $commentAttributes[0]->newInstance()->comment;
                    }

                    // Reflect on the method
                    if ($reflectionClass->hasMethod($action)) {
                        $reflectionMethod = $reflectionClass->getMethod($action);

                        // Check for AutoDocComment at method level
                        $methodAttributes = $reflectionMethod->getAttributes(AutoDocComment::class);
                        if (!empty($methodAttributes)) {
                            $comment = $methodAttributes[0]->newInstance()->comment;
                        }

                        // Check for RequestModelAttribute and extract the model structure
                        $requestModelAttributes = $reflectionMethod->getAttributes(RequestModelAttribute::class);
                        if (!empty($requestModelAttributes)) {
                            $modelClass = $requestModelAttributes[0]->newInstance()->model;

                            // Reflect on the model class and extract its properties
                            $modelReflection = new \ReflectionClass($modelClass);
                            $properties = $modelReflection->getProperties();
                            $modelStructure = [];

                            foreach ($properties as $property) {
                                $modelStructure[] = [
                                    'name' => $property->getName(),
                                    'type' => (string) $property->getType() ?: 'mixed'
                                ];
                            }

                            $requestModelStructure = [
                                'class' => $modelClass,
                                'properties' => $modelStructure
                            ];
                        }
                    }

                    // Add the route to filtered routes
                    $filteredRoutes[] = [
                        'method' => $method,
                        'path' => $path,
                        'controller' => $reflectionClass->getName(),
                        'action' => $action,
                        'comment' => $comment,
                        'requestModel' => $requestModelStructure
                    ];
                }
            }
        }
        //var_dump($filteredRoutes);      
        $response->sendTemplate(__DIR__."/../Templates/doc.html",["data"=>$filteredRoutes],true);
    }

}




?>
