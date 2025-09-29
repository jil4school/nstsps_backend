<?php
require_once __DIR__ . '/controller/db.php';

class MasterFile
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getStudentByUserId($user_id)
    {
        $sql = "SELECT s.*, p.program_name
            FROM `student_info(master_file)` s
            LEFT JOIN program p ON s.program_id = p.program_id
            LEFT JOIN student_account sa ON s.user_id = sa.user_id
            WHERE s.user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateStudent($data)
    {
        $sql = "UPDATE `student_info(master_file)` SET
        program_id = :program_id,
        surname = :surname,
        first_name = :first_name,
        middle_name = :middle_name,
        gender = :gender,
        nationality = :nationality,
        civil_status = :civil_status,
        religion = :religion,
        birthday = :birthday,
        birthplace = :birthplace,
        street = :street,
        barangay = :barangay,
        region = :region,
        municipality = :municipality,
        mobile_number = :mobile_number,
        guardian_surname = :guardian_surname,
        guardian_first_name = :guardian_first_name,
        relation_with_the_student = :relation_with_the_student,
        guardian_mobile_number = :guardian_mobile_number,
        guardian_email = :guardian_email
    WHERE master_file_id = :master_file_id";

        $stmt = $this->conn->prepare($sql);


        return $stmt->execute([
            ':program_id' => $data['program_id'] ?? null,
            ':surname' => $data['surname'] ?? null,
            ':first_name' => $data['first_name'] ?? null,
            ':middle_name' => $data['middle_name'] ?? null,
            ':gender' => $data['gender'] ?? null,
            ':nationality' => $data['nationality'] ?? null,
            ':civil_status' => $data['civil_status'] ?? null,
            ':religion' => $data['religion'] ?? null,
            ':birthday' => $data['birthday'] ?? null,
            ':birthplace' => $data['birthplace'] ?? null,
            ':street' => $data['street'] ?? null,
            ':barangay' => $data['barangay'] ?? null,
            ':region' => $data['region'] ?? null,
            ':municipality' => $data['municipality'] ?? null,
            ':mobile_number' => $data['mobile_number'] ?? null,
            ':guardian_surname' => $data['guardian_surname'] ?? null,
            ':guardian_first_name' => $data['guardian_first_name'] ?? null,
            ':relation_with_the_student' => $data['relation_with_the_student'] ?? null,
            ':guardian_mobile_number' => $data['guardian_mobile_number'] ?? null,
            ':guardian_email' => $data['guardian_email'] ?? null,
            ':master_file_id' => $data['master_file_id'] ?? null,
        ]);
    }
    public function getAllStudents()
    {
        $sql = "SELECT s.*, 
                    p.program_name, 
                    sa.email,
                    sa.is_first_login
                FROM `student_info(master_file)` s
                LEFT JOIN student_account sa 
                    ON s.user_id = sa.user_id
                LEFT JOIN program p 
                    ON s.program_id = p.program_id
                ORDER BY s.surname ASC;
                ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertStudent($data)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Insert into student_account
            $sqlAccount = "INSERT INTO student_account (email, password, role) 
                       VALUES (:email, :password, :role)";
            $stmtAccount = $this->conn->prepare($sqlAccount);
            $stmtAccount->execute([
                ':email' => $data['email'],
                ':password' => null,
                ':role' => 'student'
            ]);
            $user_id = $this->conn->lastInsertId();

            // 2. Insert into student_info(master_file)
            $sqlInfo = "INSERT INTO `student_info(master_file)` (
            user_id, student_id, program_id, surname, first_name, middle_name,
            gender, nationality, civil_status, religion, birthday, birthplace,
            street, barangay, region, municipality, mobile_number,
            guardian_surname, guardian_first_name, relation_with_the_student,
            guardian_mobile_number, guardian_email
        ) VALUES (
            :user_id, :student_id, :program_id, :surname, :first_name, :middle_name,
            :gender, :nationality, :civil_status, :religion, :birthday, :birthplace,
            :street, :barangay, :region, :municipality, :mobile_number,
            :guardian_surname, :guardian_first_name, :relation_with_the_student,
            :guardian_mobile_number, :guardian_email
        )";
            $stmtInfo = $this->conn->prepare($sqlInfo);
            $stmtInfo->execute([
                ':user_id' => $user_id,
                ':student_id' => $data['student_id'] ?? null,
                ':program_id' => $data['program_id'] ?? null,
                ':surname' => $data['surname'] ?? null,
                ':first_name' => $data['first_name'] ?? null,
                ':middle_name' => $data['middle_name'] ?? null,
                ':gender' => $data['gender'] ?? null,
                ':nationality' => $data['nationality'] ?? null,
                ':civil_status' => $data['civil_status'] ?? null,
                ':religion' => $data['religion'] ?? null,
                ':birthday' => $data['birthday'] ?? null,
                ':birthplace' => $data['birthplace'] ?? null,
                ':street' => $data['street'] ?? null,
                ':barangay' => $data['barangay'] ?? null,
                ':region' => $data['region'] ?? null,
                ':municipality' => $data['municipality'] ?? null,
                ':mobile_number' => $data['mobile_number'] ?? null,
                ':guardian_surname' => $data['guardian_surname'] ?? null,
                ':guardian_first_name' => $data['guardian_first_name'] ?? null,
                ':relation_with_the_student' => $data['relation_with_the_student'] ?? null,
                ':guardian_mobile_number' => $data['guardian_mobile_number'] ?? null,
                ':guardian_email' => $data['guardian_email'] ?? null,
            ]);
            $master_file_id = $this->conn->lastInsertId();

            // 3. Insert into student_info(registration)
            $sqlReg = "INSERT INTO `student_info(registration)` 
            (user_id, master_file_id, registration_date, school_year, year_level, sem, program_id)
            VALUES (:user_id, :master_file_id, :registration_date, :school_year, :year_level, :sem, :program_id)";
            $stmtReg = $this->conn->prepare($sqlReg);
            $registration_date = date('Y-m-d');
            $stmtReg->execute([
                ':user_id' => $user_id,
                ':master_file_id' => $master_file_id,
                ':registration_date' => $registration_date,
                ':school_year' => $data['school_year'] ?? null,
                ':year_level' => $data['year_level'] ?? null,
                ':sem' => $data['sem'] ?? null,
                ':program_id' => $data['program_id'] ?? null
            ]);
            $registration_id = $this->conn->lastInsertId();

            // 4. Insert into reg_courses for each course in program_courses
            $sqlCourses = "SELECT course_id FROM program_courses 
                       WHERE program_id = :program_id
                         AND year_level = :year_level
                         AND sem = :sem";
            $stmtCourses = $this->conn->prepare($sqlCourses);
            $stmtCourses->execute([
                ':program_id' => $data['program_id'],
                ':year_level' => $data['year_level'],
                ':sem' => $data['sem']
            ]);
            $courses = $stmtCourses->fetchAll(PDO::FETCH_ASSOC);

            $sqlRegCourse = "INSERT INTO reg_courses 
            (user_id, master_file_id, registration_id, course_id)
            VALUES (:user_id, :master_file_id, :registration_id, :course_id)";
            $stmtRegCourse = $this->conn->prepare($sqlRegCourse);

            foreach ($courses as $course) {
                $stmtRegCourse->execute([
                    ':user_id' => $user_id,
                    ':master_file_id' => $master_file_id,
                    ':registration_id' => $registration_id,
                    ':course_id' => $course['course_id']
                ]);
            }

            // 🚫 Removed Step 5: Insert into accounting

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Insert failed: " . $e->getMessage());
            return false;
        }
    }

    public function insertMultipleStudents(array $students)
    {
        try {
            $this->conn->beginTransaction();

            // Prepare reusable statements once
            $accountSql = "INSERT INTO student_account (email, password, role) VALUES (:email, :password, :role)";
            $accountStmt = $this->conn->prepare($accountSql);

            $infoSql = "INSERT INTO `student_info(master_file)` (
            user_id, student_id, program_id, surname, first_name, middle_name,
            gender, nationality, civil_status, religion, birthday, birthplace,
            street, barangay, region, municipality, mobile_number,
            guardian_surname, guardian_first_name, relation_with_the_student,
            guardian_mobile_number, guardian_email
        ) VALUES (
            :user_id, :student_id, :program_id, :surname, :first_name, :middle_name,
            :gender, :nationality, :civil_status, :religion, :birthday, :birthplace,
            :street, :barangay, :region, :municipality, :mobile_number,
            :guardian_surname, :guardian_first_name, :relation_with_the_student,
            :guardian_mobile_number, :guardian_email
        )";
            $infoStmt = $this->conn->prepare($infoSql);

            foreach ($students as $student) {
                // 1. Insert account
                $accountStmt->execute([
                    ':email' => $student['email'],
                    ':password' => $student['password'] ?? null,
                    ':role' => $student['role'] ?? 'student'
                ]);
                $userId = $this->conn->lastInsertId();

                // 2. Insert master file
                $infoStmt->execute([
                    ':user_id' => $userId,
                    ':student_id' => $student['student_id'] ?? null,
                    ':program_id' => $student['program_id'] ?? null,
                    ':surname' => $student['surname'] ?? null,
                    ':first_name' => $student['first_name'] ?? null,
                    ':middle_name' => $student['middle_name'] ?? null,
                    ':gender' => $student['gender'] ?? null,
                    ':nationality' => $student['nationality'] ?? null,
                    ':civil_status' => $student['civil_status'] ?? null,
                    ':religion' => $student['religion'] ?? null,
                    ':birthday' => $student['birthday'] ?? null,
                    ':birthplace' => $student['birthplace'] ?? null,
                    ':street' => $student['street'] ?? null,
                    ':barangay' => $student['barangay'] ?? null,
                    ':region' => $student['region'] ?? null,
                    ':municipality' => $student['municipality'] ?? null,
                    ':mobile_number' => $student['mobile_number'] ?? null,
                    ':guardian_surname' => $student['guardian_surname'] ?? null,
                    ':guardian_first_name' => $student['guardian_first_name'] ?? null,
                    ':relation_with_the_student' => $student['relation_with_the_student'] ?? null,
                    ':guardian_mobile_number' => $student['guardian_mobile_number'] ?? null,
                    ':guardian_email' => $student['guardian_email'] ?? null,
                ]);
                $masterFileId = $this->conn->lastInsertId();

                // 3. Registration insert
                $registrationSql = "INSERT INTO `student_info(registration)` 
                (user_id, master_file_id, registration_date, school_year, year_level, sem, program_id)
                VALUES (:user_id, :master_file_id, :registration_date, :school_year, :year_level, :sem, :program_id)";
                $regStmt = $this->conn->prepare($registrationSql);
                $regStmt->execute([
                    ':user_id' => $userId,
                    ':master_file_id' => $masterFileId,
                    ':registration_date' => date('Y-m-d'),
                    ':school_year' => $student['school_year'] ?? null,
                    ':year_level' => $student['year_level'] ?? null,
                    ':sem' => $student['sem'] ?? null,
                    ':program_id' => $student['program_id'] ?? null
                ]);
                $registrationId = $this->conn->lastInsertId();

                // 4. Insert courses
                $courseSql = "SELECT course_id 
                        FROM program_courses 
                        WHERE program_id = :program_id 
                            AND year_level = :year_level 
                            AND sem = :sem";
                $courseStmt = $this->conn->prepare($courseSql);

                $courseStmt->execute([
                    ':program_id' => $student['program_id'],
                    ':year_level' => $student['year_level'],
                    ':sem' => $student['sem']
                ]);

                $courses = $courseStmt->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($courses)) {
                    $regCourseSql = "INSERT INTO reg_courses 
                    (user_id, master_file_id, registration_id, course_id)
                    VALUES (:user_id, :master_file_id, :registration_id, :course_id)";
                    $regCourseStmt = $this->conn->prepare($regCourseSql);

                    foreach ($courses as $course) {
                        $regCourseStmt->execute([
                            ':user_id' => $userId,
                            ':master_file_id' => $masterFileId,
                            ':registration_id' => $registrationId,
                            ':course_id' => $course['course_id']
                        ]);
                    }
                } else {
                    // Optional: log if no courses matched
                    error_log("No courses found for program_id={$student['program_id']}, year_level={$student['year_level']}, sem={$student['sem']}");
                }

                // 🚫 Removed Step 5: Insert accounting
            }

            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Batch insert failed: " . $e->getMessage());
            return false;
        }
    }


    public function fetchPendingEmails()
    {
        $sql = "SELECT s.*, p.program_name, sa.email
            FROM `student_info(master_file)` s
            LEFT JOIN student_account sa ON s.user_id = sa.user_id
            LEFT JOIN program p ON s.program_id = p.program_id
            WHERE sa.email NOT LIKE :myEmail
              AND sa.email NOT LIKE :nstEmail";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':myEmail' => '%@my.nst.edu.ph',
            ':nstEmail' => '%@nst.edu.ph'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchCreatedEmails()
    {
        $sql = "SELECT s.*, p.program_name, sa.email
            FROM `student_info(master_file)` s
            LEFT JOIN student_account sa ON s.user_id = sa.user_id
            LEFT JOIN program p ON s.program_id = p.program_id
            WHERE sa.email LIKE :myEmail";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':myEmail' => '%@my.nst.edu.ph'
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getLatestEnrollmentWithMaster($user_id)
    {
        $sql = "SELECT 
    rr.registration_id,
    rr.registration_date,
    rr.school_year,
    rr.year_level,
    rr.sem,
    rr.program_id AS rr_program_id,
    s.master_file_id,
    s.student_id,
    s.program_id AS s_program_id,
    s.surname,
    s.first_name,
    s.middle_name,
    s.gender,
    s.nationality,
    s.civil_status,
    s.religion,
    s.birthday,
    s.birthplace,
    s.street,
    s.barangay,
    s.region,
    s.municipality,
    s.mobile_number,
    s.guardian_surname,
    s.guardian_first_name,
    s.relation_with_the_student,
    s.guardian_mobile_number,
    s.guardian_email,
    p.program_name
FROM `student_info(registration)` rr
LEFT JOIN `student_info(master_file)` s ON rr.user_id = s.user_id
LEFT JOIN program p ON rr.program_id = p.program_id
WHERE rr.user_id = :user_id
ORDER BY rr.registration_date DESC
LIMIT 1";


        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
