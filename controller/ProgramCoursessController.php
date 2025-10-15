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

    // ✅ check if filters are provided
    if (isset($_GET['program_id'], $_GET['year_level'], $_GET['sem'])) {
        $program_id = $_GET['program_id'];
        $year_level = $_GET['year_level'];
        $sem = $_GET['sem'];

        try {
            $data = $program->getFilteredProgramCourses($program_id, $year_level, $sem);
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to fetch filtered courses", "details" => $e->getMessage()]);
        }
        exit;
    }

    // fallback for old version (only program_id)
    if (isset($_GET['program_id'])) {
        $program_id = $_GET['program_id'];

        try {
            $data = $program->getProgramCourses($program_id);
            echo json_encode($data);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(["error" => "Failed to fetch program courses", "details" => $e->getMessage()]);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(["error" => "Missing parameters"]);
    exit;
}
