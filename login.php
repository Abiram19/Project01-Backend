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

$database = new DatabaseConnection();
$db = $database->getConnection();
$user = new User($db);

$data = json_decode(file_get_contents("php://input"), true);
$user->username = $data['user'];
$user->password = $data['pass'];
$rememberMe = isset($data['rememberMe']) ? $data['rememberMe'] : false;

$response = $user->login($rememberMe);
echo json_encode($response);
