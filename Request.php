<?php

require_once __DIR__ . '/controller/db.php';

class Request
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function submitRequest($data, $file = null): bool
    {
        $targetDir = "../uploads/";
        $fileName = null;

        if ($file && isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {
            $fileName = uniqid() . '_' . basename($file["name"]);
            $targetFilePath = $targetDir . $fileName;

            if (!move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return false; // file upload failed
            }
        }

        $sql = "INSERT INTO request_form 
        (user_id, master_file_id, request, request_remarks, request_purpose, mode_of_payment, receipt) 
        VALUES 
        (:user_id, :master_file_id, :request, :request_remarks, :request_purpose, :mode_of_payment, :receipt)";

$stmt = $this->conn->prepare($sql);

return $stmt->execute([
    ':user_id' => $data['user_id'],
    ':master_file_id' => $data['master_file_id'],
    ':request' => $data['request'],
    ':request_remarks' => $data['request_remarks'],
    ':request_purpose' => $data['request_purpose'],
    ':mode_of_payment' => $data['mode_of_payment'],
    ':receipt' => $fileName
]);

    }
}
