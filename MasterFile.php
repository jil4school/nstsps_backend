<?php
// MasterFile.php

require_once __DIR__ . '/controller/db.php';

class MasterFile
{
    private $conn;

    public function __construct()
    {
        global $pdo; // Use the global PDO object from db.php
        $this->conn = $pdo;
    }

    public function getStudentByUserId($user_id)
    {
        $sql = "SELECT * FROM `student_info(master_file)` WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
