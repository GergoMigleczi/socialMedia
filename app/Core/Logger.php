<?php
namespace Core;
class Logger {
    private $logFile;
    private $timeFormat = 'Y-m-d H:i:s';
    
    public function __construct($logFileName = 'app_error.log') {
        $this->logFile = APP_ROOT . '/logs/' . $logFileName;
    
        // Create logs directory if it doesn't exist
        if (!is_dir(APP_ROOT . '/logs')) {
            mkdir(APP_ROOT . '/logs', 0777, true);
        }
    }
    
    public function info($message) {
        $this->log('INFO', $message);
    }
    
    public function error($message, $exception = null) {
        if ($exception) {
            $message .= ' Exception: ' . $exception->getMessage();
            $message .= ' at ' . $exception->getFile() . ':' . $exception->getLine();
        }
        $this->log('ERROR', $message);
    }
    
    public function debug($message) {
        $this->log('DEBUG', $message);
    }
    
    public function warning($message) {
        $this->log('WARNING', $message);
    }
    
    private function log($level, $message) {
        $date = date($this->timeFormat);
        $logMessage = "[$date] [$level] $message" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
    
    public function logArray($level, $array) {
        $message = print_r($array, true);
        $this->log($level, $message);
    }
    
    public function clearLog() {
        file_put_contents($this->logFile, '');
    }

    public static function debugToJavascriptConsole($message) {
        echo "<script>console.log('PHP Debug: $message');</script>";
    }

    public static function errorToJavascriptConsole($message) {
        $escapedMessage = addslashes(str_replace(["\r\n", "\r", "\n"], ' ', $message));
        echo "<script>console.error('PHP Error: $escapedMessage');</script>";
    }
}