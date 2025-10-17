<?php
require_once __DIR__ . '/controller/db.php';

class Schedule
{
    private $conn;

    public function __construct()
    {
        global $pdo;
        $this->conn = $pdo;
    }

    public function insertSchedule($data)
    {
        try {
            $this->conn->beginTransaction();

            $program_id = $data['program_id'] ?? null;
            $year_level = $data['year_level'] ?? null;
            $sem = $data['semester'] ?? null;
            $school_year = $data['school_year'] ?? null;
            $schedules = $data['schedules'] ?? [];

            if (!$program_id || !$year_level || !$sem || !$school_year || !is_array($schedules)) {
                throw new Exception("Missing required fields or schedules array");
            }

            $insertSql = "
                INSERT INTO schedules 
                (program_id, year_level, sem, school_year, day, course_id, start_time, end_time)
                VALUES (:program_id, :year_level, :sem, :school_year, :day, :course_id, :start_time, :end_time)
            ";
            $stmt = $this->conn->prepare($insertSql);

            $inserted = 0;
            foreach ($schedules as $sched) {
                $day = $sched['day'] ?? null;
                $start = $sched['start'] ?? null;
                $end = $sched['end'] ?? null;
                $course_id = $sched['course_id'] ?? null;

                if (!$day || !$start || !$end || !$course_id) continue;

                $stmt->execute([
                    ':program_id' => $program_id,
                    ':year_level' => $year_level,
                    ':sem' => $sem,
                    ':school_year' => $school_year,
                    ':day' => $day,
                    ':course_id' => $course_id,
                    ':start_time' => $start,
                    ':end_time' => $end
                ]);

                $inserted++;
            }

            $this->conn->commit();

            return [
                "success" => true,
                "message" => "$inserted schedule(s) inserted successfully",
                "inserted_count" => $inserted
            ];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ["success" => false, "error" => $e->getMessage()];
        }
    }

    public function getSchedule($program_id, $year_level, $sem, $school_year)
    {
        try {
            $query = "
            SELECT 
                s.schedule_id,
                s.program_id,
                p.program_name,
                s.year_level,
                s.sem,
                s.school_year,
                s.day,
                s.start_time,
                s.end_time,
                s.course_id,
                c.course_description
            FROM schedules s
            INNER JOIN courses c ON s.course_id = c.course_id
            INNER JOIN program p ON s.program_id = p.program_id
            WHERE s.program_id = :program_id
              AND s.year_level = :year_level
              AND s.sem = :sem
              AND s.school_year = :school_year
            ORDER BY FIELD(s.day, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
                     s.start_time ASC
        ";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':program_id' => $program_id,
                ':year_level' => $year_level,
                ':sem' => $sem,
                ':school_year' => $school_year
            ]);

            $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$schedules) {
                return ["success" => true, "schedules" => [], "message" => "No schedules found"];
            }

            return ["success" => true, "schedules" => $schedules];
        } catch (Exception $e) {
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
    public function deleteSchedule($schedule_id)
    {
        try {
            $stmt = $this->conn->prepare("DELETE FROM schedules WHERE schedule_id = :schedule_id");
            $stmt->execute([':schedule_id' => $schedule_id]);

            if ($stmt->rowCount() > 0) {
                return ["success" => true, "message" => "Schedule deleted successfully"];
            } else {
                return ["success" => false, "error" => "No schedule found with the given ID"];
            }
        } catch (Exception $e) {
            return ["success" => false, "error" => $e->getMessage()];
        }
    }
}
