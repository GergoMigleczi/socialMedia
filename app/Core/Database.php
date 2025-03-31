<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Database handler using PDO with singleton pattern
 * 
 * Provides a secure and consistent interface for database operations including:
 * - Prepared statements with parameter binding
 * - Multiple result fetching modes
 * - Transaction support
 * - Connection management
 */
class Database {
    /** @var Database|null Singleton instance */
    private static $instance = null;
    
    /** @var PDO Database connection handle */
    private $dbh;
    
    /** @var \PDOStatement Prepared statement handle */
    private $stmt;
    
    /** @var string Last error message */
    private $error;

    /**
     * Private constructor to enforce singleton pattern
     * 
     * @throws PDOException If database connection fails
     */
    private function __construct() {
        // Create DSN (Data Source Name) string
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        
        // Connection options
        $options = [
            PDO::ATTR_PERSISTENT => true,          // Persistent connection
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, // Default fetch mode
            PDO::ATTR_EMULATE_PREPARES => false    // Use real prepared statements
        ];

        try {
            // Create PDO instance
            $this->dbh = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Store error and rethrow to prevent invalid database state
            $this->error = $e->getMessage();
            throw new PDOException("Database connection failed: " . $this->error);
        }
    }

    /**
     * Get singleton instance of Database
     * 
     * @return Database The database instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /**
     * Prepare a SQL query for execution
     * 
     * @param string $sql The SQL query
     * @return void
     */
    public function query(string $sql): void {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Bind a value to a parameter in the prepared statement
     * 
     * @param string $param Parameter identifier
     * @param mixed $value Value to bind
     * @param int|null $type PDO parameter type (auto-detected if null)
     * @return void
     */
    public function bind(string $param, $value, ?int $type = null): void {
        // Auto-detect parameter type if not specified
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Execute the prepared statement
     * 
     * @return bool True on success, false on failure
     */
    public function execute(): bool {
        try {
            return $this->stmt->execute();
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new PDOException("Query execution failed: " . $this->error);
        }
    }

    /**
     * Get result set as array of objects
     * 
     * @return array Array of result objects
     */
    public function resultSetObj(): array {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get result set as associative array
     * 
     * @return array Associative array of results
     */
    public function resultSetAssoc(): array {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single result as object
     * 
     * @return object|null Result object or null if no results
     */
    public function single(): ?object {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ) ?: null;
    }

    /**
     * Get number of affected rows from last operation
     * 
     * @return int Number of affected rows
     */
    public function rowCount(): int {
        return $this->stmt->rowCount();
    }

    /**
     * Get last inserted ID
     * 
     * @return string Last inserted row ID
     */
    public function getLastInsertId(): string {
        return $this->dbh->lastInsertId();
    }

    /**
     * Begin a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function beginTransaction(): bool {
        return $this->dbh->beginTransaction();
    }

    /**
     * Commit a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function commit(): bool {
        return $this->dbh->commit();
    }

    /**
     * Roll back a database transaction
     * 
     * @return bool True on success, false on failure
     */
    public function rollBack(): bool {
        return $this->dbh->rollBack();
    }

    /**
     * Get the last error message
     * 
     * @return string|null Error message or null if no error
     */
    public function getError(): ?string {
        return $this->error;
    }
}