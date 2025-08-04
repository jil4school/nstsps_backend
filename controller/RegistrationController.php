<?php

require_once __DIR__ . '/../Registration.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$registration = new Registration();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['registration_id'])) {
        $registration_id = $_GET['registration_id'];
        $data = $registration->getStudentByRegistrationId($registration_id);

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No student data found for that registration_id"]);
            http_response_code(404);
        }
        exit;
    }

    if (isset($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $data = $registration->getStudentByUserId($user_id);

        if ($data && count($data) > 0) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "Student not found"]);
            http_response_code(404);
        }
        exit;
    }

    echo json_encode(["error" => "Missing parameters"]);
    http_response_code(400);
}


