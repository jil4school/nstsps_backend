<?php
// Registration.php

require_once __DIR__ . '/controller/db.php';

class Registration
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getStudentByUserId($user_id)
    {
        $sql = "SELECT 
                s.*, 
                m.surname AS last_name, 
                m.first_name, 
                m.middle_name,
                m.program_id, p.program_name
            FROM `student_info(registration)` s
            LEFT JOIN `student_info(master_file)` m ON s.user_id = m.user_id
            LEFT JOIN program p ON m.program_id = p.program_id
            WHERE s.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // ← important: fetchAll
    }
    public function getStudentByRegistrationId($registration_id)
{
    $sql = "SELECT 
                s.*,
                s.school_year,
                m.surname AS last_name, 
                m.first_name, 
                m.middle_name,
                m.program_id, 
                p.program_name
            FROM `student_info(registration)` s
            LEFT JOIN `student_info(master_file)` m ON s.user_id = m.user_id
            LEFT JOIN program p ON m.program_id = p.program_id
            WHERE s.registration_id = :registration_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC); // just one row
}

}
