<?php

// ProgramCoursessController.php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../Program.php';

$program = new Program();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['program_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing program_id parameter"]);
        exit;
    }

    $program_id = $_GET['program_id'];

    try {
        $data = $program->getProgramCourses($program_id);

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No courses found for this program"]);
            http_response_code(404);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to fetch program courses",
            "details" => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
