<?php
// MasterFileController.php

require_once __DIR__ . '/../MasterFile.php';


// Set response headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if (!isset($_GET['user_id']) || empty($_GET['user_id'])) {
    echo json_encode(["error" => "Missing user_id parameter"]);
    http_response_code(400);
    exit;
}

$user_id = $_GET['user_id'];

$masterFile = new MasterFile();
$data = $masterFile->getStudentByUserId($user_id);

if ($data) {
    echo json_encode($data);
} else {
    echo json_encode(["error" => "Student not found"]);
    http_response_code(404);
}
