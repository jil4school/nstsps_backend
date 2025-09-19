<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/LoginController.php';

function sendJsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['user_id']) || !isset($input['new_password'])) {
    sendJsonResponse(['error' => 'User ID and new password are required.'], 400);
}

$loginController = new LoginController();
$success = $loginController->changeFirstLoginPassword($input['user_id'], $input['new_password']);

if ($success) {
    sendJsonResponse(['success' => true, 'message' => 'Password updated, first login complete']);
} else {
    sendJsonResponse(['error' => 'Failed to update password'], 500);
}
