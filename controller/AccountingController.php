<?php

require_once __DIR__ . '/../Accounting.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$accounting = new Accounting();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get user_id from query string
    if (!isset($_GET['user_id']) || !is_numeric($_GET['user_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing or invalid user_id"]);
        exit;
    }

    $user_id = intval($_GET['user_id']);

    try {
        $data = $accounting->getAccountingByUserId($user_id);

        if ($data && count($data) > 0) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No accounting records found"]);
            http_response_code(404);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to fetch accounting records",
            "details" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Method not allowed"]);
}
