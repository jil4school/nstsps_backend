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
                return false;
            }
        }

        $sql = "INSERT INTO request_form 
        (user_id, master_file_id, request, request_remarks, request_purpose, mode_of_payment, receipt, status) 
        VALUES 
        (:user_id, :master_file_id, :request, :request_remarks, :request_purpose, :mode_of_payment, :receipt, :status)";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id' => $data['user_id'],
            ':master_file_id' => $data['master_file_id'],
            ':request' => $data['request'],
            ':request_remarks' => $data['request_remarks'],
            ':request_purpose' => $data['request_purpose'],
            ':mode_of_payment' => $data['mode_of_payment'],
            ':receipt' => $fileName,
            ':status' => 'Pending'   // 🔥 always pending when new
        ]);
    }

    public function getAllRequests()
    {
        $sql = "SELECT 
                rf.*, 
                s.student_id, 
                s.first_name, 
                s.middle_name, 
                s.surname AS last_name,
                p.program_name,
                sa.email,
                r.year_level,
                rf.request_remarks,
                rf.request_purpose
            FROM request_form rf
            LEFT JOIN `student_info(master_file)` s 
                ON rf.user_id = s.user_id
            LEFT JOIN program p 
                ON s.program_id = p.program_id
            LEFT JOIN student_account sa 
                ON s.user_id = sa.user_id
            LEFT JOIN (
                SELECT r1.user_id, r1.year_level
                FROM `student_info(registration)` r1
                WHERE r1.registration_id = (
                    SELECT MAX(r2.registration_id)
                    FROM `student_info(registration)` r2
                    WHERE r2.user_id = r1.user_id
                )
            ) r ON s.user_id = r.user_id
            ORDER BY rf.request_id DESC";

        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function updateRequestStatus($requestId, $status): bool
    {
        $sql = "UPDATE request_form 
            SET status = :status 
            WHERE request_id = :request_id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':request_id' => $requestId
        ]);
    }
    public function getRequestsByUserId($user_id)
    {
        $sql = "SELECT request, status 
            FROM request_form 
            WHERE user_id = :user_id
            ORDER BY request_id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
