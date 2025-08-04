<?php

require_once __DIR__ . '/../Program.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$program = new Program();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $data = $program->getAllPrograms();

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No programs found"]);
            http_response_code(404);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to fetch programs",
            "details" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Method not allowed"]);
}
