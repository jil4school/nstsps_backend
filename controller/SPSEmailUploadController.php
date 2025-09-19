<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (preg_match('/^http:\/\/localhost:\d+$/', $origin)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}

header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../SPSEmailUpload.php';

$spsEmail = new SPSEmailUpload();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['file'])) {
        echo json_encode(["error" => "No file uploaded"]);
        http_response_code(400);
        exit;
    }

    $filePath = $_FILES['file']['tmp_name'];
    try {
        $result = $spsEmail->processExcel($filePath);
        echo json_encode([
            "message" => "Excel processed successfully",
            "updated" => $result
        ]);
    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
        http_response_code(500);
    }
}
