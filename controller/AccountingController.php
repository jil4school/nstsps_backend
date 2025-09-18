<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../Accounting.php';

$accounting = new Accounting();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            $user_id = intval($_GET['user_id']);
            $data = $accounting->getAccountingByUserId($user_id);

            // always return as single object
            echo json_encode([
                "total_balance" => (float)($data['total_balance'] ?? 0),
                "latest_school_year" => $data['latest_school_year'] ?? null,
                "latest_sem" => $data['latest_sem'] ?? null
            ]);
        } else {
            $data = $accounting->getAllAccountingRecords();
            echo json_encode($data);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "error" => "Failed to fetch accounting records",
            "details" => $e->getMessage()
        ]);
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

    if (isset($data['action']) && $data['action'] === 'update_balance') {
        $balance_id = $data['balance_id'] ?? null;
        $amount_paid = $data['amount_paid'] ?? null;

        if ($balance_id === null || !is_numeric($balance_id) || $amount_paid === null) {
            http_response_code(400);
            echo json_encode(["success" => false, "error" => "Missing or invalid parameters (balance_id, amount_paid)"]);
            exit;
        }

        try {
            $result = $accounting->updateBalance((int)$balance_id, (float)$amount_paid);
            echo json_encode(["success" => true, "message" => "Balance updated"]);
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

http_response_code(405);
echo json_encode(["error" => "Method not allowed"]);
