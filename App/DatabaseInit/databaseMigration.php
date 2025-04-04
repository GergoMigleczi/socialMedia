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

    public function generateRandomPosts() {
        $visibilityOptions = ['public', 'friends', 'private'];
        $totalPosts = 500;
        $profileId = 2;
    
        $startTimestamp = strtotime('2024-04-04 00:00:00');
        $endTimestamp = strtotime('2025-04-04 23:59:59');
    
        $stmt = $this->conn->prepare(
            "INSERT INTO POSTS (profile_id, content, visibility, created_at) VALUES (?, ?, ?, ?)"
        );
    
        if (!$stmt) {
            $this->logger->error("Failed to prepare statement: " . $this->conn->error);
            return;
        }
    
        for ($i = 0; $i < $totalPosts; $i++) {
            $randomTimestamp = rand($startTimestamp, $endTimestamp);
            $createdAt = date('Y-m-d H:i:s', $randomTimestamp);
            $visibility = $visibilityOptions[array_rand($visibilityOptions)];
            $content = "Generated post #" . ($i + 1);
    
            $stmt->bind_param("isss", $profileId, $content, $visibility, $createdAt);
    
            if (!$stmt->execute()) {
                $this->logger->error("Insert failed for post #" . ($i + 1) . ": " . $stmt->error);
            } else {
                $this->logger->info("Inserted post #" . ($i + 1));
            }
        }
    
        $stmt->close();
    }   
}

// Run the migration
$migration = new DatabaseMigration();
$migration->runSQLFile(__DIR__ . '/database.sql');
$migration->logger->debug("Database setup completed successfully!");
$migration->runSQLFile(__DIR__ . '/testData.sql');
$migration->logger->debug("Database test data inserted successfully!");
$migration->generateRandomPosts();

