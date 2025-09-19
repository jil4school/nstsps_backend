<?php
// Allow requests from any origin
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
header("Content-Type: application/json");
include './controller/db.php';
include './controller/ForgotPasswordController.php';

$controller = new ForgotPasswordController($pdo); // pass PDO, not $conn


$data = json_decode(file_get_contents("php://input"), true);
$type = $data['type'] ?? '';
$email = $data['email'] ?? '';
$code = $data['code'] ?? '';
$new_password = $data['new_password'] ?? '';

switch ($type) {
    case "checkUser":
        $exists = $controller->checkUser($email);
        echo json_encode(['exists' => $exists]);
        break;

    case "sendCode":
        if (!$email || !$code) {
            echo json_encode(['success' => false, 'error' => 'Email or code missing']);
            exit;
        }
        $success = $controller->sendCode($email, $code);
        echo json_encode(['success' => $success]);
        break;

    case "verifyCode":
        if (!$email || !$code) {
            echo json_encode(['success' => false, 'error' => 'Email or code missing']);
            exit;
        }
        $verified = $controller->verifyCode($email, $code);
        echo json_encode(['success' => $verified]);
        break;

    case "changePassword":
        if (!$email || !$new_password) {
            echo json_encode(['success' => false, 'error' => 'Email or new password missing']);
            exit;
        }
        $success = $controller->changePassword($email, $new_password);
        echo json_encode(['success' => $success]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid type']);
        break;
}
