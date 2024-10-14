<?php
// Allow from any origin
header('Access-Control-Allow-Origin: *');

// Allow specific methods
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');

// Allow specific headers
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests (OPTIONS method)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit; // Exit early for OPTIONS requests
}

header('Content-Type: application/json');

require_once 'BookingCalendar1.php';

$calendar = new BookingCalendar();

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'get_calendar':
            $month = $_GET['month'];
            $year = $_GET['year'];
            echo json_encode($calendar->buildCalendar($month, $year));
            break;

        case 'check_slots':
            $date = $_GET['date'];
            echo json_encode($calendar->checkSlots($date));
            break;

        case 'get_bookings':
            $date = $_GET['date'];
            echo json_encode($calendar->getBookings($date));
            break;

        case 'get_timeslot_status':
            $date = $_GET['date'];
            echo json_encode($calendar->getTimeslotStatus($date));
            break;

        case 'add_booking':
            $data = json_decode(file_get_contents('php://input'), true);

            // Debug: Log the incoming data
            file_put_contents('php://stderr', print_r($data, true));

            if (!$data) {
                echo json_encode(['status' => 'error', 'message' => 'Invalid JSON payload']);
                break;
            }

            // Sanitize and validate input data
            $name = filter_var($data['name'], FILTER_SANITIZE_STRING);
            $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
            $phone = filter_var($data['phone'], FILTER_SANITIZE_STRING);
            $vehicleModel = filter_var($data['vehicleModel'], FILTER_SANITIZE_STRING);
            $vehicleNumber = filter_var($data['vehicleNumber'], FILTER_SANITIZE_STRING);
            $timeslot = filter_var($data['timeslot'], FILTER_SANITIZE_STRING);
            $date = filter_var($data['date'], FILTER_SANITIZE_STRING);

            // Establishing a PDO connection
            $dsn = "mysql:host=localhost;dbname=autocare_lanka;charset=utf8";
            try {
                $pdo = new PDO($dsn, "root", "");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                // Prepare and execute the SQL query using PDO
                $query = "INSERT INTO bookings1 (name, email, phone, vehicle_model, vehicle_number, timeslot, date) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$name, $email, $phone, $vehicleModel, $vehicleNumber, $timeslot, $date]);

                echo json_encode(['status' => 'success']);
            } catch (PDOException $e) {
                echo json_encode(['status' => 'error', 'message' => 'Booking failed: ' . $e->getMessage()]);
            }
            break;

        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
}
