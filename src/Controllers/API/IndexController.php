<?php 

namespace App\Controllers\API;
use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Router\Route;
use App\Models\Data\ApiResponseModel;
use App\Middleware\DemoMiddleware;
use Internal\Middleware\RouteMiddleware;

#[RouteMiddleware(DemoMiddleware::class)]
class IndexController
{
    #[Route("GET","/")]
    public function home(Request $request, Response $response){

        $response->setStatusCode(200);
        $response->json(new ApiResponseModel(true,"Welcome to simple php"));
    }
}




?>
