<?php
require_once 'DatabaseConnection.php';

class Messages
{
    private $connection;

    public function __construct()
    {
        $dbConnection = new DatabaseConnection();
        $this->connection = $dbConnection->getConnection();
    }

    public function getMessages()
    {
        $query = "SELECT * FROM messages";
        $result = $this->connection->query($query);

        if (!$result) {
            return array("error" => $this->connection->error);
        }

        $messages = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        }

        return $messages;
    }
}

header('Content-Type: application/json');

$messages = new Messages();
echo json_encode($messages->getMessages());
