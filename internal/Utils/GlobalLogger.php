<?php
namespace Internal\Utils;


/***
 * Usage
 * $logger->log("Application started.");
 * $logger->log("This is a warning!", "WARNING");
 * $logger->log("An error occurred!", "ERROR");
 */
class GlobalLogger
{
    private static string $logFile = __DIR__."/../../logs/.log";

    public function __construct()
    {
    }

    public static function log(string $message, string $level = 'INFO'): void
    {
        $date = date("Y-m-d H:i:s");
        $logEntry = "[$date] [$level]: $message```" . PHP_EOL;
        file_put_contents(GlobalLogger::$logFile, $logEntry, FILE_APPEND);
    }
    public static function clear(): void
    {
        file_put_contents(GlobalLogger::$logFile, "");
    }
    public static function load(): string
    {
        return file_get_contents(GlobalLogger::$logFile);
    }
}
 




