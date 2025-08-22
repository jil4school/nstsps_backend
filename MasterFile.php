<?php
// MasterFile.php

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
        $sql = "SELECT s.*, p.program_name, sa.email
            FROM `student_info(master_file)` s
            LEFT JOIN student_account sa ON s.user_id = sa.user_id
            LEFT JOIN program p ON s.program_id = p.program_id";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function insertStudent($data)
    {
        $sql = "INSERT INTO `student_info(master_file)` (
        student_id,
        program_id,
        surname,
        first_name,
        middle_name,
        gender,
        nationality,
        civil_status,
        religion,
        birthday,
        birthplace,
        street,
        barangay,
        region,
        municipality,
        mobile_number,
        guardian_surname,
        guardian_first_name,
        relation_with_the_student,
        guardian_mobile_number,
        guardian_email
    ) VALUES (
        :student_id,
        :program_id,
        :surname,
        :first_name,
        :middle_name,
        :gender,
        :nationality,
        :civil_status,
        :religion,
        :birthday,
        :birthplace,
        :street,
        :barangay,
        :region,
        :municipality,
        :mobile_number,
        :guardian_surname,
        :guardian_first_name,
        :relation_with_the_student,
        :guardian_mobile_number,
        :guardian_email
    )";

        $stmt = $this->conn->prepare($sql);

        try {
            return $stmt->execute([
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
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            return false;
        }
    }

    public function insertMultipleStudents(array $students)
{
    $sql = "INSERT INTO `student_info(master_file)` (
        student_id,
        program_id,
        surname,
        first_name,
        middle_name,
        gender,
        nationality,
        civil_status,
        religion,
        birthday,
        birthplace,
        street,
        barangay,
        region,
        municipality,
        mobile_number,
        guardian_surname,
        guardian_first_name,
        relation_with_the_student,
        guardian_mobile_number,
        guardian_email
    ) VALUES (
        :student_id,
        :program_id,
        :surname,
        :first_name,
        :middle_name,
        :gender,
        :nationality,
        :civil_status,
        :religion,
        :birthday,
        :birthplace,
        :street,
        :barangay,
        :region,
        :municipality,
        :mobile_number,
        :guardian_surname,
        :guardian_first_name,
        :relation_with_the_student,
        :guardian_mobile_number,
        :guardian_email
    )";

    $stmt = $this->conn->prepare($sql);

    try {
        $this->conn->beginTransaction();

        foreach ($students as $student) {
            $stmt->execute([
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
        }

        $this->conn->commit();
        return true;
    } catch (PDOException $e) {
        $this->conn->rollBack();
        error_log("Batch insert failed: " . $e->getMessage());
        return false;
    }
}

}
