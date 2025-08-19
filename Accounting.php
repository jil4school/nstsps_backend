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
}
