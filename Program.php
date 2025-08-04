<?php
// Program.php

require_once __DIR__ . '/controller/db.php';

class Program
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getAllPrograms()
    {
        $sql = "SELECT * FROM program ORDER BY program_name ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
