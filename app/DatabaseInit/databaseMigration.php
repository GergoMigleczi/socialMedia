<?php
namespace App\DabaseInit;

use App\Core\Logger;

class DatabaseMigration {
    private $conn;
    public $logger;

    public function __construct() {
        $this->logger = new Logger('databaseMigration.log');
        $this->conn = new \mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function runSQLFile($filePath) {
        if (!file_exists($filePath)) {
            die("SQL file not found: $filePath");
        }

        $sql = file_get_contents($filePath);
        if (!$sql) {
            die("Failed to read SQL file.");
        }

        // Execute multi-query
        try{
            if ($this->conn->multi_query($sql)) {
                do {
                    // Store first result set (if any)
                    if ($result = $this->conn->store_result()) {
                        $result->free();
                    }
                } while ($this->conn->next_result());
            } else {
                $this->logger->error("Error executing SQL: " . $this->conn->error);
                exit;
            }
        }catch(\Exception $e){
            $this->logger->error($e);
        }
        
    }
}

// Run the migration
$migration = new DatabaseMigration();
$migration->runSQLFile(__DIR__ . '/database.sql');
$migration->logger->debug("Database setup completed successfully!");
$migration->runSQLFile(__DIR__ . '/testData.sql');
$migration->logger->debug("Database test data inserted successfully!");


