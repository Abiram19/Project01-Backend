<?php
require_once 'DatabaseConnection.php';

class Booking
{
    private $connection;

    public function __construct()
    {
        $dbConnection = new DatabaseConnection();
        $this->connection = $dbConnection->getConnection();
    }

    public function getBookings4()
    {
        $query = "SELECT * FROM bookings";
        $result = $this->connection->query($query);

        $bookings = array();
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
        }

        return $bookings;
    }
}

header('Content-Type: application/json');

$booking = new Booking();
echo json_encode($booking->getBookings4());
