<?php 

namespace App\Controllers\API;

use Internal\Router\Route;

class IndexController
{
    
    #[Route("GET","/aa")]
    public function aa(){
        echo "Hello Bye";
    }
}




?>
