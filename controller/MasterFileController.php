<?php

require_once __DIR__ . '/../MasterFile.php';
// CORS fix
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'http://localhost:5173') {
    header("Access-Control-Allow-Origin: $origin");
} else {
    header("Access-Control-Allow-Origin: http://localhost");
}

header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


$masterFile = new MasterFile();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $user_id = $_GET['user_id'];
        $data = $masterFile->getStudentByUserId($user_id);

        if ($data) {
            echo json_encode($data);
        } else {
            echo json_encode(["error" => "Student not found"]);
            http_response_code(404);
        }
    } else {
        $data = $masterFile->getAllStudents();
        echo json_encode($data);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // ---------- Batch insert ----------
    if (isset($input['students']) && is_array($input['students'])) {
        try {
            $result = $masterFile->insertMultipleStudents($input['students']);
            if ($result) {
                echo json_encode(["message" => "Batch students inserted successfully"]);
            } else {
                echo json_encode(["error" => "Failed to insert batch students"]);
                http_response_code(500);
            }
        } catch (Exception $e) {
            echo json_encode(["error" => "Batch insert error: " . $e->getMessage()]);
            http_response_code(500);
        }
        exit;
    }

    // ---------- Update single student ----------
    if (isset($input['master_file_id']) && !empty($input['master_file_id'])) {
        $result = $masterFile->updateStudent($input);
        if ($result) {
            echo json_encode(["message" => "Student info updated successfully"]);
        } else {
            echo json_encode(["error" => "Failed to update student info"]);
            http_response_code(500);
        }
        exit;
    }

    // ---------- Insert single student ----------
    $result = $masterFile->insertStudent($input);
    if ($result) {
        echo json_encode(["message" => "Student inserted successfully"]);
    } else {
        echo json_encode(["error" => "Failed to insert student"]);
        http_response_code(500);
    }
}

