<?php
require_once __DIR__ . '/controller/db.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class SPSEmailUpload
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function processExcel($filePath)
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $updatedCount = 0;

        for ($i = 2; $i <= count($rows); $i++) {
            $studentId = $rows[$i]['A'] ?? null;
            $email     = $rows[$i]['F'] ?? null;
            $password  = $rows[$i]['G'] ?? null;


            if (!$studentId || !$email) {
                continue;
            }

            $sqlUser = "SELECT user_id FROM `student_info(master_file)` WHERE student_id = :student_id";
            $stmtUser = $this->conn->prepare($sqlUser);
            $stmtUser->execute([':student_id' => $studentId]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userId = $user['user_id'];

                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                $sqlUpdate = "UPDATE student_account 
              SET email = :email, password = :password, is_first_login = 1
              WHERE user_id = :user_id";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':email' => $email,
                    ':password' => $hashedPassword,
                    ':user_id' => $userId
                ]);



                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
