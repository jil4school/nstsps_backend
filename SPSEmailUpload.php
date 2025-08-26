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

        // skip header (row 1)
        for ($i = 2; $i <= count($rows); $i++) {
            $studentId = $rows[$i]['A'] ?? null;
            $email     = $rows[$i]['F'] ?? null;  // Email is in column F
            $password  = $rows[$i]['G'] ?? null;  // Password is in column G


            if (!$studentId || !$email) {
                continue; // skip if missing essentials
            }

            // find user_id from student_info(master_file)
            $sqlUser = "SELECT user_id FROM `student_info(master_file)` WHERE student_id = :student_id";
            $stmtUser = $this->conn->prepare($sqlUser);
            $stmtUser->execute([':student_id' => $studentId]);
            $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $userId = $user['user_id'];

                // update student_account
                $sqlUpdate = "UPDATE student_account SET email = :email, password = :password WHERE user_id = :user_id";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute([
                    ':email' => $email,
                    ':password' => $password, // ⚠️ hash if needed
                    ':user_id' => $userId
                ]);

                $updatedCount++;
            }
        }

        return $updatedCount;
    }
}
