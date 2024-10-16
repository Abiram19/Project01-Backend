<?php

class DatabaseConnection
{
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $database = 'autocare_lanka';
    protected $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8";

        try {
            // Establish the connection using PDO
            $this->connection = new PDO($dsn, $this->user, $this->password);
            // Set PDO to throw exceptions for better error handling
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log('Database connection successful');
        } catch (PDOException $e) {
            // Handle connection failure
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
