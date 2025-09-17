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
    public function saveGrade($user_id, $master_file_id, $registration_id, $course_id, $grade)
    {
        // Check if grade already exists
        $checkSql = "SELECT grades_id 
                     FROM `student_info(grades)` 
                     WHERE user_id = :user_id 
                       AND master_file_id = :master_file_id 
                       AND registration_id = :registration_id 
                       AND course_id = :course_id";

        $checkStmt = $this->conn->prepare($checkSql);
        $checkStmt->execute([
            ':user_id' => $user_id,
            ':master_file_id' => $master_file_id,
            ':registration_id' => $registration_id,
            ':course_id' => $course_id
        ]);

        if ($checkStmt->rowCount() > 0) {
            // Update grade
            $updateSql = "UPDATE `student_info(grades)` 
                          SET grade = :grade 
                          WHERE user_id = :user_id 
                            AND master_file_id = :master_file_id 
                            AND registration_id = :registration_id 
                            AND course_id = :course_id";

            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->execute([
                ':grade' => $grade,
                ':user_id' => $user_id,
                ':master_file_id' => $master_file_id,
                ':registration_id' => $registration_id,
                ':course_id' => $course_id
            ]);

            return ["action" => "updated", "course_id" => $course_id];
        } else {
            // Insert new grade
            $insertSql = "INSERT INTO `student_info(grades)` 
                          (user_id, master_file_id, registration_id, course_id, grade) 
                          VALUES (:user_id, :master_file_id, :registration_id, :course_id, :grade)";

            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->execute([
                ':user_id' => $user_id,
                ':master_file_id' => $master_file_id,
                ':registration_id' => $registration_id,
                ':course_id' => $course_id,
                ':grade' => $grade
            ]);

            return ["action" => "inserted", "course_id" => $course_id];
        }
    }
}
