<?php
require 'vendor/autoload.php';
require_once __DIR__ . '/controller/db.php'; // this gives us $pdo

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// SQL query: exclude emails ending in @my.nst.edu.ph and @nst.edu.ph
$sql = "
    SELECT 
        si.student_id,
        si.surname AS last_name,
        si.first_name,
        p.program_name
    FROM student_account sa
    INNER JOIN `student_info(master_file)` si ON sa.user_id = si.user_id
    INNER JOIN program p ON si.program_id = p.program_id
    WHERE sa.email NOT LIKE '%@my.nst.edu.ph'
      AND sa.email NOT LIKE '%@nst.edu.ph'
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Define headers
$headers = ["Student ID", "Last Name", "First Name", "Program", "Email", "Password"];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '1', $header);
    $col++;
}

// Write student rows
$rowNum = 2;
foreach ($students as $student) {
    $sheet->setCellValue("A" . $rowNum, $student['student_id']);
    $sheet->setCellValue("B" . $rowNum, $student['last_name']);
    $sheet->setCellValue("C" . $rowNum, $student['first_name']);
    $sheet->setCellValue("D" . $rowNum, $student['program_name']);
    // Columns E and F left blank for Email + Password
    $rowNum++;
}

// Export file
$writer = new Xlsx($spreadsheet);
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="SPSEmail.xlsx"');
$writer->save("php://output");
exit;
