<?php
// Accounting.php

require_once __DIR__ . '/controller/db.php';

class Accounting
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getAccountingByUserId($user_id)
    {
        $sql = "
        SELECT 
            SUM(a.balance) AS total_balance,
            MAX(r.school_year) AS latest_school_year,
            MAX(r.sem) AS latest_sem
        FROM accounting a
        LEFT JOIN `student_info(registration)` r 
            ON a.registration_id = r.registration_id
        WHERE a.user_id = :user_id
    ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllAccountingRecords()
    {
        $sql = "
        SELECT 
            a.balance_id,
            a.total_tuition_fee,
            a.amount_paid,
            mf.student_id,
            mf.surname,
            mf.first_name,
            mf.middle_name,
            p.program_name,
            r.year_level,
            r.school_year,
            r.sem,
            a.balance
        FROM accounting a
        LEFT JOIN `student_info(master_file)` mf 
            ON a.master_file_id = mf.master_file_id
        LEFT JOIN `student_info(registration)` r 
            ON a.registration_id = r.registration_id
        LEFT JOIN program p 
            ON r.program_id = p.program_id
        ORDER BY mf.surname, mf.first_name;
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update accounting row by balance_id by adding $amount_paid to amount_paid and
     * recalculating balance = total_tuition_fee - new_amount_paid
     *
     * @param int $balance_id
     * @param float $amount_paid (amount to add)
     * @return bool
     * @throws Exception
     */
    public function updateBalance($balance_id, $amount_paid)
    {
        try {
            $this->conn->beginTransaction();

            // 1) Fetch accounting row (now contains total_tuition_fee directly)
            $sql = "SELECT balance_id, total_tuition_fee, amount_paid 
                FROM accounting 
                WHERE balance_id = :balance_id 
                LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':balance_id', $balance_id, PDO::PARAM_INT);
            $stmt->execute();
            $acc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$acc) {
                throw new Exception("Accounting row not found for balance_id: " . $balance_id);
            }

            $tuition_fee = isset($acc['total_tuition_fee']) ? (float)$acc['total_tuition_fee'] : 0.0;
            $existing_paid = isset($acc['amount_paid']) ? (float)$acc['amount_paid'] : 0.0;
            $add_paid = (float)$amount_paid;

            // 2) Add to existing paid
            $new_amount_paid = $existing_paid + $add_paid;

            // 3) Compute new balance
            $new_balance = $tuition_fee - $new_amount_paid;
            if ($new_balance < 0) $new_balance = 0;

            // 4) Update accounting row
            $updateSql = "UPDATE accounting 
                      SET amount_paid = :amount_paid, balance = :balance 
                      WHERE balance_id = :balance_id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindValue(':amount_paid', $new_amount_paid);
            $updateStmt->bindValue(':balance', $new_balance);
            $updateStmt->bindValue(':balance_id', $balance_id, PDO::PARAM_INT);
            $updateStmt->execute();

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function insertAccountingRecord($data)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Find student in master_file by student_id + names
            $stmtMF = $this->conn->prepare("
            SELECT master_file_id, user_id, surname, first_name, middle_name
            FROM `student_info(master_file)`
            WHERE student_id = :student_id
            LIMIT 1
        ");
            $stmtMF->execute([':student_id' => $data['student_id']]);
            $student = $stmtMF->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                throw new Exception("Student not found with student_id " . $data['student_id']);
            }

            // strict name check
            if (
                strtolower(trim($student['surname'])) !== strtolower(trim($data['surname'])) ||
                strtolower(trim($student['first_name'])) !== strtolower(trim($data['first_name'])) ||
                strtolower(trim($student['middle_name'] ?? '')) !== strtolower(trim($data['middle_name'] ?? ''))
            ) {
                throw new Exception("Student name does not match records.");
            }

            $user_id = $student['user_id'];
            $master_file_id = $student['master_file_id'];

            // 2. Find registration_id
            $stmtReg = $this->conn->prepare("
            SELECT registration_id
            FROM `student_info(registration)`
            WHERE user_id = :user_id
              AND master_file_id = :master_file_id
              AND program_id = :program_id
              AND year_level = :year_level
              AND sem = :sem
              AND school_year = :school_year
            LIMIT 1
        ");
            $stmtReg->execute([
                ':user_id' => $user_id,
                ':master_file_id' => $master_file_id,
                ':program_id' => $data['program_id'],
                ':year_level' => $data['year_level'],
                ':sem' => $data['sem'],
                ':school_year' => $data['school_year']
            ]);
            $registration = $stmtReg->fetch(PDO::FETCH_ASSOC);

            if (!$registration) {
                throw new Exception("Registration not found for the given details.");
            }

            $registration_id = $registration['registration_id'];

            // 3. Compute balance
            $total_tuition_fee = (float)$data['total_tuition_fee'];
            $amount_paid = (float)$data['amount_paid'];
            $balance = $total_tuition_fee - $amount_paid;
            if ($balance < 0) $balance = 0;

            // 4. Insert into accounting
            $stmtAcc = $this->conn->prepare("
            INSERT INTO accounting 
            (user_id, master_file_id, registration_id, total_tuition_fee, balance, mode_of_payment, amount_paid)
            VALUES (:user_id, :master_file_id, :registration_id, :total_tuition_fee, :balance, :mode_of_payment, :amount_paid)
        ");
            $stmtAcc->execute([
                ':user_id' => $user_id,
                ':master_file_id' => $master_file_id,
                ':registration_id' => $registration_id,
                ':total_tuition_fee' => $total_tuition_fee,
                ':balance' => $balance,
                ':mode_of_payment' => $data['mode_of_payment'],
                ':amount_paid' => $amount_paid
            ]);

            $this->conn->commit();

            return [
                "success" => true,
                "balance_id" => $this->conn->lastInsertId(),
                "balance" => $balance
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
}
