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
    public function getStudentByRegistrationAndStudentId($registration_id, $student_id, $user_id)
{
    // First query: student + program info
    $sql = "SELECT 
                s.*,
                s.school_year,
                m.surname AS last_name, 
                m.first_name, 
                m.middle_name,
                m.program_id, 
                p.program_name
            FROM `student_info(registration)` s
            LEFT JOIN `student_info(master_file)` m 
                   ON s.student_id = m.student_id
            LEFT JOIN program p 
                   ON m.program_id = p.program_id
            WHERE s.registration_id = :registration_id
              AND s.student_id = :student_id
              AND s.user_id = :user_id";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
    $stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();

    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        return null;
    }

    // Second query: courses for that registration_id & student_id & user_id
    $sqlCourses = "SELECT 
                        c.course_code,
                        c.course_description,
                        c.unit
                   FROM reg_courses rc
                   INNER JOIN courses c ON rc.course_id = c.course_id
                   INNER JOIN `student_info(registration)` s 
                           ON rc.registration_id = s.registration_id
                   WHERE rc.registration_id = :registration_id
                     AND s.student_id = :student_id
                     AND s.user_id = :user_id"; 

    $stmtCourses = $this->conn->prepare($sqlCourses);
    $stmtCourses->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
    $stmtCourses->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $stmtCourses->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmtCourses->execute();
    $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

    $student['course_codes'] = array_column($courses, 'course_code');
    $student['course_descriptions'] = array_column($courses, 'course_description');
    $student['units'] = array_column($courses, 'unit');

    return $student;
}

}
