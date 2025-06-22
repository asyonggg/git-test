<?php
session_start();
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    http_response_code(403);
    die('Forbidden: You do not have permission to access this resource.');
}

// --- DATABASE CONNECTION ---
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "db_jru_pulse";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    die('Database connection failed.');
}

// --- GET PARAMETERS ---
$startDate = $_GET['startDate'] ?? date('Y-m-d', strtotime('-30 days'));
$endDate = $_GET['endDate'] ?? date('Y-m-d');
$office = $_GET['office'] ?? 'all';
$format = $_GET['format'] ?? 'csv';
$columns = isset($_GET['columns']) ? explode(',', $_GET['columns']) : ['all'];

// --- BUILD QUERY ---
$sql = "SELECT * FROM tbl_feedback_submissions WHERE date_submitted BETWEEN ? AND ?";
$params = [$startDate, $endDate];
$types = "ss";

if ($office !== 'all') {
    $sql .= " AND office = ?";
    $params[] = $office;
    $types .= "s";
}
$sql .= " ORDER BY submission_timestamp DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// --- DATA PROCESSING & FILE GENERATION ---
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Feedback Report');

// Define all possible headers
$allHeaders = [
    'id' => 'ID', 'student_no' => 'Student No', 'division' => 'Division', 'course' => 'Course',
    'office' => 'Office', 'service' => 'Service', 'service_outcome' => 'Service Outcome',
    'speed_of_service' => 'Speed of Service', 'processing_instruction' => 'Processing Instruction',
    'staff_professionalism_courtesy' => 'Staff Courtesy', 'suggestions' => 'Suggestions',
    'date_submitted' => 'Date Submitted', 'time_submitted' => 'Time Submitted', 'submission_timestamp' => 'Timestamp'
];

// Determine which headers to use
$selectedHeaders = [];
if (in_array('all', $columns)) {
    $selectedHeaders = $allHeaders;
} else {
    foreach ($columns as $columnKey) {
        if (isset($allHeaders[$columnKey])) {
            $selectedHeaders[$columnKey] = $allHeaders[$columnKey];
        }
    }
}

// Write headers to the sheet
$col = 'A';
foreach ($selectedHeaders as $header) {
    $sheet->setCellValue($col . '1', $header);
    $sheet->getStyle($col . '1')->getFont()->setBold(true);
    $col++;
}

// Write data rows
$rowNum = 2;
while ($row = $result->fetch_assoc()) {
    $col = 'A';
    foreach ($selectedHeaders as $key => $header) {
        $sheet->setCellValue($col . $rowNum, $row[$key]);
        $col++;
    }
    $rowNum++;
}

// Autosize columns
foreach (range('A', $col) as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// --- OUTPUT FILE ---
$filename = "JRU_PULSE_Report_" . date('Y-m-d') . ($office !== 'all' ? '_' . str_replace(' ', '_', $office) : '');

if ($format === 'xlsx') {
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
} else { // Default to CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
    header('Cache-Control: max-age=0');
    $writer = new Csv($spreadsheet);
}

$writer->save('php://output');

$stmt->close();
$conn->close();
exit;

