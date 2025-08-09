<?php

require_once __DIR__ . '/../Request.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$request = new Request();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_POST['user_id'] ?? null,
        'student_id' => $_POST['student_id'] ?? null,
        'request' => $_POST['request'] ?? null,
        'request_remarks' => $_POST['request_remarks'] ?? null,
        'request_purpose' => $_POST['request_purpose'] ?? null,
        'mode_of_payment' => $_POST['mode_of_payment'] ?? null,
    ];

    $file = $_FILES['receipt'] ?? null;

    $result = $request->submitRequest($data, $file);

    if ($result) {
        echo json_encode(["message" => "Request submitted successfully."]);
        http_response_code(200);
    } else {
        echo json_encode(["error" => "Failed to submit request."]);
        http_response_code(500);
    }
}
