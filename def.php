<?php


try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (\Throwable $th) {
    echo "Failed to load ENV: See .env.example For Sample.";
    die();
}
//// Add Definitions as needed 
define('DEBUG_MODE', ($_ENV["ENABLE_DEBUG"] ?? "false") === "true");

