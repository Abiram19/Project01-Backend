<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once 'DatabaseConnection.php';
include_once 'User.php';

// Initialize database connection using PDO
$database = new DatabaseConnection();
$db = $database->getConnection();
$user = new User($db);

// Decode the input data
$data = json_decode(file_get_contents("php://input"), true);
$user->username = $data['user'];
$user->password = $data['pass'];
$rememberMe = isset($data['rememberMe']) ? $data['rememberMe'] : false;

// Call the login method
$response = $user->login($rememberMe);

if (isset($response['success'])) {
    // Include the username and userrole in the response
    $response['username'] = $_SESSION['username'];
    $response['userrole'] = $_SESSION['userrole'];
}

echo json_encode($response);
