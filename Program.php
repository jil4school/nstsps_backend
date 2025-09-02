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

    public function getProgramCourses($program_id)
    {
        $sql = "
        SELECT 
            pc.program_courses_id,
            pc.program_id,
            pc.year_level,
            pc.sem,
            c.course_id,
            c.course_code,
            c.course_description,
            c.unit
        FROM program_courses pc
        LEFT JOIN courses c ON pc.course_id = c.course_id
        WHERE pc.program_id = :program_id
        ORDER BY 
            CASE pc.year_level
                WHEN '1st Year' THEN 1
                WHEN '2nd Year' THEN 2
                WHEN '3rd Year' THEN 3
                WHEN '4th Year' THEN 4
                ELSE 5
            END,
            CASE pc.sem
                WHEN 'First Sem' THEN 1
                WHEN 'Second Sem' THEN 2
                ELSE 3
            END;
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':program_id', $program_id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
