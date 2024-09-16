<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

include 'DatabaseConnection.php';
include 'User.php';

$response = array();
$data = json_decode(file_get_contents("php://input"));

if (isset($data->user) && isset($data->email) && isset($data->pass) && isset($data->cpass)) {

    $database = new DatabaseConnection();
    $db = $database->getConnection();

    $user = new User($db);
    $user->username = $data->user;
    $user->email = $data->email;
    $user->password = $data->pass;

    if (!preg_match("/^[a-zA-Z0-9@_]{4,20}$/", $user->username)) {
        $response['error'] = 'Username must be between 4 and 20 characters and may include letters, numbers, @, and _.';
        echo json_encode($response);
        exit();
    }

    if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = 'Invalid email format.';
        echo json_encode($response);
        exit();
    }

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*[@!?\/\-_])[A-Za-z\d@!?\/\-_]{8,20}$/", $user->password)) {
        $response['error'] = 'Password must contain 8 to 20 characters and it should contain at least one uppercase letter, at least one lowercase letter, and at least one special character (@,!,?,/,_,-).';
        echo json_encode($response);
        exit();
    }

    if ($user->password !== $data->cpass) {
        $response['error'] = 'Passwords do not match.';
        echo json_encode($response);
        exit();
    }

    // Check if email already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $user->email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $response['error'] = 'Email already registered.';
        echo json_encode($response);
        $stmt->close();
        exit();
    }

    // Check if username already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $user->username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $response['error'] = 'Username already taken.';
        echo json_encode($response);
        $stmt->close();
        exit();
    }

    // Register the user
    $result = $user->register();
    echo json_encode($result);

    $stmt->close();
    $db->close();
} else {
    $response['error'] = 'Please fill all the fields.';
    echo json_encode($response);
}
