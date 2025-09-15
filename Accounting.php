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
        $sql = "SELECT 
                    a.*,
                    r.school_year,
                    r.sem
                FROM accounting a
                LEFT JOIN `student_info(registration)` r 
                    ON a.registration_id = r.registration_id
                WHERE a.user_id = :user_id;
                ";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getAllAccountingRecords()
    {
        $sql = "
        SELECT 
            a.balance_id,
            a.tuition_id,
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
     * recalculating balance = tuition_fee - total_amount_paid
     *
     * @param int $balance_id
     * @param float $amount_paid (amount to add)
     * @return bool
     * @throws Exception
     */
    public function updateBalance($balance_id, $amount_paid)
    {
        // Start transaction (safe)
        try {
            $this->conn->beginTransaction();

            // 1) fetch accounting row by balance_id
            $sql = "SELECT balance_id, tuition_id, amount_paid FROM accounting WHERE balance_id = :balance_id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':balance_id', $balance_id, PDO::PARAM_INT);
            $stmt->execute();
            $acc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$acc) {
                throw new Exception("Accounting row not found for balance_id: " . $balance_id);
            }

            $tuition_id = (int)$acc['tuition_id'];
            $existing_paid = isset($acc['amount_paid']) ? (float)$acc['amount_paid'] : 0.0;
            $add_paid = (float)$amount_paid;
            $new_amount_paid = $existing_paid + $add_paid;

            // 2) fetch tuition_fee from program_tuition_fee using tuition_id
            $sqlTuition = "SELECT tuition_fee FROM program_tuition_fee WHERE tuition_id = :tuition_id LIMIT 1";
            $stmtTuition = $this->conn->prepare($sqlTuition);
            $stmtTuition->bindParam(':tuition_id', $tuition_id, PDO::PARAM_INT);
            $stmtTuition->execute();
            $tuitionRow = $stmtTuition->fetch(PDO::FETCH_ASSOC);

            if (!$tuitionRow) {
                throw new Exception("Tuition row not found for tuition_id: " . $tuition_id);
            }

            $tuition_fee = (float)$tuitionRow['tuition_fee'];

            // 3) compute new balance
            $new_balance = $tuition_fee - $new_amount_paid;
            if ($new_balance < 0) $new_balance = 0;

            // 4) update accounting row by balance_id
            $updateSql = "UPDATE accounting SET amount_paid = :amount_paid, balance = :balance WHERE balance_id = :balance_id";
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
}
