<?php

require_once __DIR__ . '/../ProgramCourses.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$programCourse = new ProgramCourse();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['program_id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Missing program_id parameter"]);
        exit;
    }

    $programId = intval($_GET['program_id']);

    try {
        $data = $programCourse->getCoursesByProgramId($programId);

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(["message" => "No courses found for this program"]);
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
