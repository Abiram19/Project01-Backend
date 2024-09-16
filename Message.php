<?php
class Message
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function insertMessage($firstName, $phone, $email, $message)
    {
        $sql = 'INSERT INTO messages (first_name, phone, email, message) VALUES (?, ?, ?, ?)';
        $stmt = $this->connection->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('ssss', $firstName, $phone, $email, $message);
            $stmt->execute();
            $stmt->close();
        } else {
            // Handle errors
            echo json_encode(['success' => false, 'message' => 'Failed to prepare statement.']);
        }
    }
}
