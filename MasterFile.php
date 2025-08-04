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
    WHERE student_id = :student_id";

        $stmt = $this->conn->prepare($sql);

        // ✅ Bind each value manually to ensure no mismatch
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
            ':student_id' => $data['student_id'] ?? null,
        ]);
    }
}
