<?php

require_once __DIR__ . '/../Registration.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$registration = new Registration();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['registration_id'], $_GET['master_file_id'])) {
        $registration_id = $_GET['registration_id'];
        $master_file_id  = $_GET['master_file_id'];
        $user_id         = $_GET['user_id'] ?? null;

        if ($user_id === null) {
            http_response_code(400);
            echo json_encode(["error" => "Missing user_id parameter"]);
            exit;
        }

        $data = $registration->getStudentByRegistrationAndStudentId($registration_id, $master_file_id, $user_id);

        if ($data) {
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No student data found for that registration_id and user_id"]);
        }
        exit;
    }

    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $data = $registration->getStudentByUserId($user_id);

        if ($data && count($data) > 0) {
            echo json_encode($data);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "Student not found"]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // ✅ Batch insert (supports both `registrations` only or `action: batch_insert`)
    if ((isset($data['registrations']) && is_array($data['registrations'])) || 
        (isset($data['action']) && $data['action'] === 'batch_insert')) {
        try {
            $registrations = $data['registrations'] ?? [];
            if (!empty($registrations)) {
                $result = $registration->insertMultipleRegistrations($registrations);
                echo json_encode(["success" => true, "message" => "Batch registrations inserted successfully"]);
            } else {
                http_response_code(400);
                echo json_encode(["success" => false, "error" => "No registrations provided"]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => "Batch insert error: " . $e->getMessage()]);
        }
        exit;
    }

    // ✅ Default: update registration
    $registration_id = $data['registration_id'] ?? null;
    $master_file_id  = $data['master_file_id'] ?? null;
    $user_id         = $data['user_id'] ?? null;
    $courses         = $data['courses'] ?? null;
    $deleted         = $data['deleted'] ?? [];

    if (!$registration_id || !$master_file_id || !$user_id || $courses === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit;
    }

    $result = $registration->updateRegistration($registration_id, $master_file_id, $user_id, $courses, $deleted);

    echo json_encode(['success' => $result]);
    exit;
}
