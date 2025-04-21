<?php
namespace App\Core;

class Model
{
    /**
     * Database connection
     * @var Database
     */
    protected $db;
    
    /**
     * Logger instance
     * @var Logger
     */
    protected $logger;
    
    /**
     * Constructor
     * 
     * @param string $logFile Optional specific log file for this model
     */
    public function __construct($logFile = 'model.log')
    {
        // Initialize database connection and logger
        $this->db = Database::getInstance();
        $this->logger = new Logger($logFile);
    }
}