<?php
// Logout.php

header("Access-Control-Allow-Origin: http://localhost:5173"); // allow frontend
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true"); // if you use cookies/sessions

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Start the session
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Send JSON response
header("Content-Type: application/json");
echo json_encode([
    "success" => true,
    "message" => "Logged out successfully"
]);
exit;
?>
