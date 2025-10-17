<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../Schedule.php';

$schedule = new Schedule();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $program_id = $_GET['program_id'] ?? null;
    $year_level = $_GET['year_level'] ?? null;
    $sem = $_GET['sem'] ?? null;
    $school_year = $_GET['school_year'] ?? null;

    if (!$program_id || !$year_level || !$sem || !$school_year) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing required query parameters"]);
        exit;
    }

    try {
        $result = $schedule->getSchedule($program_id, $year_level, $sem, $school_year);
        echo json_encode($result);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if (!is_array($data)) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Invalid JSON body"]);
        exit;
    }

    if (isset($data['action']) && $data['action'] === 'insert_schedule') {
        try {
            $result = $schedule->insertSchedule($data);
            if ($result['success']) {
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode($result);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        exit;
    }


    http_response_code(400);
    echo json_encode(["success" => false, "error" => "Invalid action"]);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents("php://input"), true);
    $schedule_id = $data['schedule_id'] ?? null;
    error_log("Deleting schedule_id: " . $schedule_id);

    if (!$schedule_id) {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Missing schedule_id"]);
        exit;
    }

    try {
        $result = $schedule->deleteSchedule($schedule_id);
        if ($result['success']) {
            echo json_encode($result);
        } else {
            http_response_code(400);
            echo json_encode($result);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["success" => false, "error" => $e->getMessage()]);
    }
    exit;
}


http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
