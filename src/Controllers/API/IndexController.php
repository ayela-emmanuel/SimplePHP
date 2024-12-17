<?php 

namespace App\Controllers\API;
use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Router\Route;
use App\Models\Data\ApiResponseModel;
use App\Middleware\DemoMiddleware;
use Exception;
use Internal\Attributes\AutoDocAttribute;
use Internal\Attributes\AutoDocComment;
use Internal\Attributes\RequestModelAttribute;
use Internal\Middleware\RouteMiddleware;
use Internal\Utils\GlobalLogger;
use Throwable;

#[AutoDocAttribute]
#[RouteMiddleware(DemoMiddleware::class)]
class IndexController
{
    #[Route("GET","/")]
    public function home(Request $request, Response $response){

        $response->setStatusCode(200);
        $response->json(new ApiResponseModel(true,"Welcome to simple php"));
    }

    #[AutoDocComment("Some Test Comment")]
    #[Route("POST","/test")]
    #[RequestModelAttribute(ApiResponseModel::class)]
    public function test(Request $request, Response $response){
        
        $response->setStatusCode(200);
        $response->json(new ApiResponseModel(true,"Welcome to simple php"));
    }

    #[AutoDocComment("Another Some Test Comment")]
    #[Route("DELETE","/test1")]
    public function test1(Request $request, Response $response){
        GlobalLogger::log("Hello World");
        $response->setStatusCode(200);
        $response->json(new ApiResponseModel(true,"Welcome to simple php"));
    }
    
}




?>
