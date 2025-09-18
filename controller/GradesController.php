<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../Grades.php';

$grades = new Grades();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['master_file_id']) && isset($_GET['registration_id'])) {
        $master_file_id = $_GET['master_file_id'];
        $registration_id = $_GET['registration_id'];

        $data = $grades->getGradesByStudentAndRegistration($master_file_id, $registration_id);

        if ($data && count($data) > 0) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No grades found for this student and registration."]);
            http_response_code(404);
        }
        exit;
    }

    echo json_encode(["error" => "Missing parameters: master_file_id and registration_id required."]);
    http_response_code(400);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input || !isset($input['grades'])) {
        echo json_encode(["error" => "Invalid payload. Expecting 'grades' array."]);
        http_response_code(400);
        exit;
    }

    $results = [];
    foreach ($input['grades'] as $g) {
        $result = $grades->saveGrade(
            $input['user_id'],
            $input['master_file_id'],
            $input['registration_id'],
            $g['course_id'],
            $g['grade']
        );
        $results[] = $result;
    }

    echo json_encode([
        "success" => true,
        "message" => "Grades processed successfully",
        "results" => $results
    ]);
}
