<?php

require_once __DIR__ . '/../Grades.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$grades = new Grades();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['student_id']) && isset($_GET['registration_id'])) {
        $student_id = $_GET['student_id'];
        $registration_id = $_GET['registration_id'];

        $data = $grades->getGradesByStudentAndRegistration($student_id, $registration_id);

        if ($data && count($data) > 0) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "No grades found for this student and registration."]);
            http_response_code(404);
        }
        exit;
    }

    echo json_encode(["error" => "Missing parameters: student_id and registration_id required."]);
    http_response_code(400);
}
