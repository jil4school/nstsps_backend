<?php

require_once __DIR__ . '/../MasterFile.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}


$masterFile = new MasterFile();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
        echo json_encode(["error" => "Missing user_id parameter"]);
        http_response_code(400);
        exit;
    }

    $user_id = $_GET['user_id'];
    $data = $masterFile->getStudentByUserId($user_id);

    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(["error" => "Student not found"]);
        http_response_code(404);
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($input['master_file_id'])) {
        echo json_encode(["error" => "Missing master_file_id"]);
        http_response_code(400);
        exit;
    }

    $result = $masterFile->updateStudent($input);

    if ($result) {
        echo json_encode(["message" => "Student info updated successfully"]);
    } else {
        echo json_encode(["error" => "Failed to update student info"]);
        http_response_code(500);
    }
}
