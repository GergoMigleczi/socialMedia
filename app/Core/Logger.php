<?php
namespace Core;

/**
 * A comprehensive logging utility that handles various log levels and output methods.
 * 
 * Provides file-based logging with different severity levels, array logging,
 * log clearing, and JavaScript console output capabilities.
 */
class Logger {
    /** @var string Path to the log file */
    private $logFile;
    
    /** @var string Format for timestamps in log entries */
    private $timeFormat = 'Y-m-d H:i:s';
    
    /**
     * Constructor - initializes the logger with specified log file.
     * 
     * @param string $logFileName Name of the log file (default: 'app_error.log')
     * @throws \RuntimeException If log directory cannot be created
     */
    public function __construct($logFileName = 'app_error.log') {
        $this->logFile = APP_ROOT . '/logs/' . $logFileName;
    
        // Ensure logs directory exists with proper permissions
        if (!is_dir(APP_ROOT . '/logs')) {
            if (!mkdir(APP_ROOT . '/logs', 0777, true)) {
                throw new \RuntimeException('Failed to create logs directory');
            }
        }
    }
    
    /**
     * Logs an informational message.
     * 
     * @param string $message The message to log
     */
    public function info($message) {
        $this->log('INFO', $message);
    }
    
    /**
     * Logs an error message with optional exception details.
     * 
     * @param string $message The error message
     * @param \Exception|null $exception Optional exception object to include details
     */
    public function error($message, $exception = null) {
        if ($exception) {
            $message .= ' Exception: ' . $exception->getMessage();
            $message .= ' at ' . $exception->getFile() . ':' . $exception->getLine();
            $message .= PHP_EOL . 'Stack Trace: ' . $exception->getTraceAsString();
        }
        $this->log('ERROR', $message);
    }
    
    /**
     * Logs a debug message.
     * 
     * @param string $message The debug message
     */
    public function debug($message) {
        $this->log('DEBUG', $message);
    }
    
    /**
     * Logs a warning message.
     * 
     * @param string $message The warning message
     */
    public function warning($message) {
        $this->log('WARNING', $message);
    }
    
    /**
     * Internal log writing method.
     * 
     * @param string $level The log level (INFO, ERROR, DEBUG, WARNING)
     * @param string $message The message to log
     * @throws \RuntimeException If log file cannot be written
     */
    private function log($level, $message) {
        // Only log if the environment is development
        if(APP_ENV != 'development') return;

        $date = date($this->timeFormat);
        $logMessage = "[$date] [$level] $message" . PHP_EOL;
        
        // Write to log file with error suppression and manual check
        if (@file_put_contents($this->logFile, $logMessage, FILE_APPEND) === false) {
            throw new \RuntimeException("Failed to write to log file: {$this->logFile}");
        }
    }
    
    /**
     * Logs an array in readable format.
     * 
     * @param string $level The log level
     * @param array $array The array to log
     */
    public function logArray($level, $array) {
        $message = print_r($array, true);
        $this->log($level, $message);
    }
    
    /**
     * Clears the log file contents.
     * 
     * @throws \RuntimeException If log file cannot be cleared
     */
    public function clearLog() {
        if (@file_put_contents($this->logFile, '') === false) {
            throw new \RuntimeException("Failed to clear log file: {$this->logFile}");
        }
    }

    /**
     * Outputs a debug message to browser's JavaScript console.
     * 
     * @param string $message The debug message to send to console
     */
    public static function debugToJavascriptConsole($message) {
        $escapedMessage = addslashes(str_replace(["\r\n", "\r", "\n"], ' ', $message));
        echo "<script>console.log('PHP Debug: $escapedMessage');</script>";
    }

    /**
     * Outputs an error message to browser's JavaScript console.
     * 
     * @param string $message The error message to send to console
     */
    public static function errorToJavascriptConsole($message) {
        $escapedMessage = addslashes(str_replace(["\r\n", "\r", "\n"], ' ', $message));
        echo "<script>console.error('PHP Error: $escapedMessage');</script>";
    }
}