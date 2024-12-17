<?php 

namespace Internal\Controllers;

use Exception;
use Internal\Http\Request;
use Internal\Http\Response;
use Internal\Router\Route;
use Internal\Utils\GlobalLogger;

class DebuggingController
{
    #[Route("GET","/dev/logs")]
    public function logs(Request $request, Response $response){

        $response->setStatusCode(200);
        $data = array_reverse(explode("```",GlobalLogger::load()));
        
        $response->sendTemplate(__DIR__."/../Templates/logs.html",["data"=>$data],true);
    }
    #[Route("DELETE","/dev/logs/clear")]
    public function clearlogs(Request $request, Response $response){

        $response->setStatusCode(200);
        GlobalLogger::clear();
        //$data = array_reverse(explode("```",GlobalLogger::load()));
        
        //$response->redirect("/dev/logs");
    }
}




?>
