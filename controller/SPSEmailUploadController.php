<?php
require_once __DIR__ . '/../SPSEmailUpload.php';

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if ($origin === 'http://localhost:5173') {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
