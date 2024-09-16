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
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);

        if ($this->connection->connect_error) {
            die('Connection failed: ' . $this->connection->connect_error);
        } else {
            error_log('Database connection successful');
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
