<?php
// Always set CORS headers before anything else
header_remove("Access-Control-Allow-Origin");
header_remove("Access-Control-Allow-Methods");
header_remove("Access-Control-Allow-Headers");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight request quickly
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

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);

// ✅ instantiate once, sa taas pa lang
$loginController = new LoginController();

// 👉 Handle deactivate
if (isset($input['action']) && $input['action'] === 'deactivate') {
    if (!isset($input['user_id'])) {
        sendJsonResponse(['error' => 'Missing user_id'], 400);
    }

    $success = $loginController->deactivateStudent($input['user_id']);

    if ($success) {
        sendJsonResponse(['success' => true, 'message' => 'Student account deactivated']);
    } else {
        sendJsonResponse(['error' => 'Failed to deactivate student'], 500);
    }
}
// 👉 Handle reactivate
if (isset($input['action']) && $input['action'] === 'reactivate') {
    if (!isset($input['user_id'])) {
        sendJsonResponse(['error' => 'Missing user_id'], 400);
    }

    $success = $loginController->reactivateStudent($input['user_id']);

    if ($success) {
        sendJsonResponse(['success' => true, 'message' => 'Student account reactivated']);
    } else {
        sendJsonResponse(['error' => 'Failed to reactivate student'], 500);
    }
}


// 👉 Handle login
if (!isset($input['email']) || !isset($input['password'])) {
    sendJsonResponse(['error' => 'Email and password are required.'], 400);
}

$response = $loginController->login($input['email'], $input['password']);

if (isset($response['error'])) {
    sendJsonResponse($response, 401);
} else {
    sendJsonResponse($response);
}
