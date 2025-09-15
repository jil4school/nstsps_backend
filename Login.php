<?php
header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/controller/LoginController.php';

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    exit;
}

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['email']) || !isset($input['password'])) {
    sendJsonResponse(['error' => 'Email and password are required.'], 400);
}

$loginController = new LoginController();
$response = $loginController->login($input['email'], $input['password']);

// Output result
if (isset($response['error'])) {
    sendJsonResponse($response, 401);
} else {
    sendJsonResponse($response);
}
