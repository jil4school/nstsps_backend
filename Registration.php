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
                m.program_id, p.program_name, m.student_id
            FROM `student_info(registration)` s
            LEFT JOIN `student_info(master_file)` m ON s.user_id = m.user_id
            LEFT JOIN program p ON m.program_id = p.program_id
            WHERE s.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getStudentByRegistrationAndStudentId($registration_id, $master_file_id, $user_id)
    {
        // First query: student + program info
        $sql = "SELECT 
                s.*,
                s.school_year,
                m.surname AS last_name, 
                m.first_name, 
                m.middle_name,
                m.program_id, 
                p.program_name,
                m.student_id
            FROM `student_info(registration)` s
            LEFT JOIN `student_info(master_file)` m 
                   ON s.master_file_id = m.master_file_id
            LEFT JOIN program p 
                   ON m.program_id = p.program_id
            WHERE s.registration_id = :registration_id
              AND s.master_file_id = :master_file_id
              AND s.user_id = :user_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
        $stmt->bindParam(':master_file_id', $master_file_id, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            return null;
        }

        // Second query: fetch courses including reg_courses_id
        $sqlCourses = "SELECT 
                        rc.reg_courses_id,
                        c.course_id,
                        c.course_code,
                        c.course_description,
                        c.unit
                   FROM reg_courses rc
                   INNER JOIN courses c ON rc.course_id = c.course_id
                   WHERE rc.registration_id = :registration_id
                     AND rc.master_file_id = :master_file_id
                     AND rc.user_id = :user_id";

        $stmtCourses = $this->conn->prepare($sqlCourses);
        $stmtCourses->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
        $stmtCourses->bindParam(':master_file_id', $master_file_id, PDO::PARAM_INT);
        $stmtCourses->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmtCourses->execute();
        $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

        // Add course info to the student array
        $student['course_codes'] = array_column($courses, 'course_code');
        $student['course_descriptions'] = array_column($courses, 'course_description');
        $student['units'] = array_column($courses, 'unit');
        $student['reg_courses_id'] = array_column($courses, 'reg_courses_id'); // ✅ crucial

        return $student;
    }

    public function updateRegistration($registration_id, $master_file_id, $user_id, $courses)
    {
        try {
            $this->conn->beginTransaction();

            foreach ($courses as $course) {
                $sql = "UPDATE reg_courses 
                       SET course_id = :course_id
                     WHERE registration_id = :registration_id 
                       AND master_file_id = :master_file_id 
                       AND user_id = :user_id 
                       AND reg_courses_id = :reg_courses_id";

                $stmt = $this->conn->prepare($sql);
                $stmt->bindParam(':course_id', $course['course_id'], PDO::PARAM_INT);
                $stmt->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
                $stmt->bindParam(':master_file_id', $master_file_id, PDO::PARAM_INT);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->bindParam(':reg_courses_id', $course['reg_courses_id'], PDO::PARAM_INT);
                // ✅ each row must know which reg_courses_id to update

                $stmt->execute();
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Update registration error: " . $e->getMessage());
            return false;
        }
    }
}
