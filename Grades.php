<?php

require_once __DIR__ . '/controller/db.php';

class Grades
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getGradesByStudentAndRegistration($master_file_id, $registration_id)
    {
        $sql = "SELECT g.*, 
               c.unit, 
               c.course_code, 
               c.course_description
        FROM `student_info(grades)` g
        LEFT JOIN courses c ON g.course_id = c.course_id
        WHERE g.master_file_id = :master_file_id AND g.registration_id = :registration_id";


        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':master_file_id', $master_file_id, PDO::PARAM_STR);
        $stmt->bindParam(':registration_id', $registration_id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
