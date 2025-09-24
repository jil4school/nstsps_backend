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

    public function updateRegistration($registration_id, $master_file_id, $user_id, $courses, $deleted = [])
    {
        try {
            $this->conn->beginTransaction();

            foreach ($courses as $course) {
                if (!empty($course['reg_courses_id'])) {
                    // 🔹 Existing row → UPDATE
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
                    $stmt->execute();
                } else {
                    // 🔹 New row → INSERT
                    $sql = "INSERT INTO reg_courses (registration_id, master_file_id, user_id, course_id)
                        VALUES (:registration_id, :master_file_id, :user_id, :course_id)";

                    $stmt = $this->conn->prepare($sql);
                    $stmt->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
                    $stmt->bindParam(':master_file_id', $master_file_id, PDO::PARAM_INT);
                    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(':course_id', $course['course_id'], PDO::PARAM_INT);
                    $stmt->execute();
                }
            }

            // ✅ Handle deletions
            if (!empty($deleted)) {
                $sqlDelete = "DELETE FROM reg_courses 
                          WHERE reg_courses_id = :reg_courses_id
                            AND registration_id = :registration_id 
                            AND master_file_id = :master_file_id 
                            AND user_id = :user_id";
                $stmtDelete = $this->conn->prepare($sqlDelete);

                foreach ($deleted as $id) {
                    $stmtDelete->bindParam(':reg_courses_id', $id, PDO::PARAM_INT);
                    $stmtDelete->bindParam(':registration_id', $registration_id, PDO::PARAM_INT);
                    $stmtDelete->bindParam(':master_file_id', $master_file_id, PDO::PARAM_INT);
                    $stmtDelete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                    $stmtDelete->execute();
                }
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Update registration error: " . $e->getMessage());
            return false;
        }
    }

    public function insertMultipleRegistrations(array $registrations)
    {
        try {
            $this->conn->beginTransaction();
            $results = [];

            foreach ($registrations as $reg) {
                // 1. Get master_file_id and user_id
                $stmtMF = $this->conn->prepare("
                SELECT master_file_id, user_id 
                FROM `student_info(master_file)` 
                WHERE student_id = :student_id 
                LIMIT 1
            ");
                $stmtMF->execute([':student_id' => $reg['student_id']]);
                $student = $stmtMF->fetch(PDO::FETCH_ASSOC);
                if (!$student) {
                    throw new Exception("Student not found for student_id " . $reg['student_id']);
                }
                $master_file_id = $student['master_file_id'];
                $user_id        = $student['user_id'];

                // 2. Get program_id
                $stmtProg = $this->conn->prepare("
                SELECT program_id 
                FROM program 
                WHERE program_name = :program 
                LIMIT 1
            ");
                $stmtProg->execute([':program' => $reg['program']]);
                $program = $stmtProg->fetch(PDO::FETCH_ASSOC);
                if (!$program) {
                    throw new Exception("Program not found: " . $reg['program']);
                }
                $program_id = $program['program_id'];

                // 3. Insert registration
                $sql = "INSERT INTO `student_info(registration)` 
                (master_file_id, user_id, registration_date, school_year, year_level, sem, program_id) 
                VALUES (:master_file_id, :user_id, :registration_date, :school_year, :year_level, :sem, :program_id)";
                $stmtInsert = $this->conn->prepare($sql);
                $stmtInsert->execute([
                    ':master_file_id'    => $master_file_id,
                    ':user_id'           => $user_id,
                    ':registration_date' => $reg['registration_date'] ?? null,
                    ':school_year'       => $reg['school_year'] ?? null,
                    ':year_level'        => $reg['year_level'] ?? null,
                    ':sem'               => $reg['sem'] ?? null,
                    ':program_id'        => $program_id,
                ]);
                $newRegId = $this->conn->lastInsertId();

                // 4. Fetch program courses and insert into reg_courses
                $stmtCoursesFetch = $this->conn->prepare("
                SELECT c.course_id, c.course_code, c.course_description, c.unit
                FROM program_courses pc
                JOIN courses c ON pc.course_id = c.course_id
                WHERE pc.program_id = :program_id 
                  AND pc.year_level = :year_level 
                  AND pc.sem = :sem
            ");
                $stmtCoursesFetch->execute([
                    ':program_id' => $program_id,
                    ':year_level' => $reg['year_level'],
                    ':sem'        => $reg['sem'],
                ]);
                $courses = $stmtCoursesFetch->fetchAll(PDO::FETCH_ASSOC);

                if ($courses) {
                    $sqlCourses = "INSERT INTO reg_courses (registration_id, master_file_id, user_id, course_id)
                               VALUES (:registration_id, :master_file_id, :user_id, :course_id)";
                    $stmtCourses = $this->conn->prepare($sqlCourses);
                    foreach ($courses as $course) {
                        $stmtCourses->execute([
                            ':registration_id' => $newRegId,
                            ':master_file_id'  => $master_file_id,
                            ':user_id'         => $user_id,
                            ':course_id'       => $course['course_id'],
                        ]);
                    }
                }

                // ✅ Collect results (no accounting insert anymore)
                $results[] = [
                    'student_id'      => $reg['student_id'],
                    'registration_id' => $newRegId,
                    'program_id'      => $program_id,
                    'year_level'      => $reg['year_level'],
                    'sem'             => $reg['sem'],
                    'courses'         => $courses,
                ];
            }

            $this->conn->commit();
            return $results;
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Batch registration insert failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }


    public function getCoursesByRegistration($registration_id, $master_file_id, $user_id)
    {
        $query = "SELECT rc.reg_courses_id,
                     c.course_id,
                     c.course_code,
                     c.course_description,
                     c.unit,
                     g.grade
              FROM `reg_courses` rc
              JOIN courses c 
                     ON rc.course_id = c.course_id
              LEFT JOIN `student_info(grades)` g 
                     ON g.registration_id = rc.registration_id
                    AND g.master_file_id = rc.master_file_id
                    AND g.user_id = rc.user_id
                    AND g.course_id = rc.course_id
              WHERE rc.registration_id = :registration_id
                AND rc.master_file_id = :master_file_id
                AND rc.user_id = :user_id";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':registration_id' => $registration_id,
            ':master_file_id' => $master_file_id,
            ':user_id' => $user_id
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
