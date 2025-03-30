<?php

namespace Core;

class Database {
    private static $instance = null;
    private $dbh;
    private $stmt;
    private $error;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME;
        $options = [
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
        ];

        try {
            $this->dbh = new \PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = \PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = \PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = \PDO::PARAM_NULL;
                    break;
                default:
                    $type = \PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute() {
        return $this->stmt->execute();
    }

    public function resultSetObj() {
        $this->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function resultSetAssoc() {
        $this->execute();
        return $this->stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function single() {
        $this->execute();
        return $this->stmt->fetch(\PDO::FETCH_OBJ);
    }

    public function rowCount() {
        return $this->stmt->rowCount();
    }

    public function getLastInsertId() {
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
}
