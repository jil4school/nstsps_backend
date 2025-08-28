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
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $requests = $request->getAllRequests();
        echo json_encode($requests);
        http_response_code(200);
    } catch (Exception $e) {
        echo json_encode(["error" => "Failed to fetch requests", "details" => $e->getMessage()]);
        http_response_code(500);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'user_id' => $_POST['user_id'] ?? null,
        'master_file_id' => $_POST['master_file_id'] ?? null,
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
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Parse raw JSON input (React/Axios sends JSON for PUT)
    $data = json_decode(file_get_contents("php://input"), true);

    $requestId = $data['request_id'] ?? null;
    $status = $data['status'] ?? null;

    if ($requestId && $status) {
        $result = $request->updateRequestStatus($requestId, $status);

        if ($result) {
            echo json_encode(["message" => "Request updated successfully."]);
            http_response_code(200);
        } else {
            echo json_encode(["error" => "Failed to update request."]);
            http_response_code(500);
        }
    } else {
        echo json_encode(["error" => "Missing request_id or status"]);
        http_response_code(400);
    }
}

