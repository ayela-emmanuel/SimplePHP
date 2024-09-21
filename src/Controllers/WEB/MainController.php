<?php 

namespace App\Controllers\WEB;
use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Router\Route;

class MainController
{
    
    #[Route("GET","/")]
    public function index(Request $request, Response $response):void
    {
        $response->setStatusCode(200);
        $response->sendTemplate("HomePage.latte");
    }

}




?>