<?php
// ProgramCourses.php

require_once __DIR__ . '/controller/db.php';

class ProgramCourse
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function getCoursesByProgramId($programId)
    {
        $sql = "
        SELECT 
            pc.course_id,
            c.course_code,
            c.course_description,
            c.unit
        FROM program_courses pc
        LEFT JOIN courses c 
            ON pc.course_id = c.course_id
        WHERE pc.program_id = :program_id
        ORDER BY c.course_description ASC
    ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':program_id', $programId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
