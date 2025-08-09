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

function sendJsonResponse($data, $status = 200)
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['user_id'], $input['old_password'], $input['new_password'], $input['confirm_password'])) {
    sendJsonResponse(['error' => 'All fields are required'], 400);
}

$user_id         = $input['user_id'];
$old_password    = $input['old_password'];
$new_password    = $input['new_password'];
$confirm_password = $input['confirm_password'];

$loginController = new LoginController();

// Step 1: Verify old password
$verifyResult = $loginController->verifyOldPassword($user_id, $old_password);

if (isset($verifyResult['error'])) {
    sendJsonResponse($verifyResult, 404);
}

if ($verifyResult['match'] === false) {
    sendJsonResponse(['error' => 'Old password is incorrect'], 401);
}

// Step 2: Check if new password matches
if ($new_password !== $confirm_password) {
    sendJsonResponse(['error' => 'New password and confirm password do not match'], 400);
}

// Step 3: Update password
$updateResult = $loginController->updatePassword($user_id, $new_password);

if ($updateResult) {
    sendJsonResponse(['success' => true, 'message' => 'Password changed successfully']);
} else {
    sendJsonResponse(['error' => 'Failed to change password'], 500);
}
