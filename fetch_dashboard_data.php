<?php
session_start();
header('Content-Type: application/json');
error_reporting(0); // Disable error reporting for production, but you can enable it for debugging

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    echo json_encode(['error' => 'Authentication Required']);
    exit;
}

// --- GET AND VALIDATE DATE RANGE ---
$startDate = isset($_GET['startDate']) ? $_GET['startDate'] : date('Y-m-d', strtotime('-6 days'));
$endDate = isset($_GET['endDate']) ? $_GET['endDate'] : date('Y-m-d');

if (!DateTime::createFromFormat('Y-m-d', $startDate) || !DateTime::createFromFormat('Y-m-d', $endDate)) {
     echo json_encode(['error' => 'Invalid date format. Please use YYYY-MM-DD.']);
    exit;
}

// --- DATABASE CONNECTION ---
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "db_jru_pulse";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// This array will hold all the data to be returned
$data = [];

// --- ROBUST DATA FETCHING ---

// 1. Total Responses for the period
$stmt = $conn->prepare("SELECT COUNT(id) as total FROM tbl_feedback_submissions WHERE date_submitted BETWEEN ? AND ?");
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$data['total_responses'] = $row ? (int)$row['total'] : 0;
$stmt->close();

// 2. Overall Satisfaction
$query = "SELECT AVG((service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4) as avg_rating 
          FROM tbl_feedback_submissions WHERE date_submitted BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$overall_satisfaction = $row ? $row['avg_rating'] : 0;
$data['overall_satisfaction'] = $overall_satisfaction ? round($overall_satisfaction, 1) : 0;
$stmt->close();

// 3. Feedback Frequency Average
$start_dt = new DateTime($startDate);
$end_dt = new DateTime($endDate);
$total_days = $end_dt->diff($start_dt)->days + 1;

if ($total_days > 0 && $data['total_responses'] > 0) {
    $data['feedback_frequency_avg'] = round($data['total_responses'] / $total_days);
} else {
    $data['feedback_frequency_avg'] = 0;
}

// 4. Rating Distribution
$query = "SELECT FLOOR((service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4) as rating, COUNT(id) as count 
          FROM tbl_feedback_submissions WHERE date_submitted BETWEEN ? AND ? GROUP BY rating";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$data['rating_distribution'] = ['5' => 0, '4' => 0, '3' => 0, '2' => 0, '1' => 0];
while($row = $result->fetch_assoc()) {
    if (isset($data['rating_distribution'][$row['rating']])) {
       $data['rating_distribution'][$row['rating']] = (int)$row['count'];
    }
}
$stmt->close();

// 5. Service Performance
$query = "SELECT AVG(service_outcome) as service_quality, AVG(speed_of_service) as response_time, AVG(staff_professionalism_courtesy) as staff_courtesy, AVG(processing_instruction) as process_efficiency 
          FROM tbl_feedback_submissions WHERE date_submitted BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$service_performance = $stmt->get_result()->fetch_assoc();
$data['service_performance'] = [
    'service_quality' => round(isset($service_performance['service_quality']) ? $service_performance['service_quality'] : 0, 1),
    'response_time' => round(isset($service_performance['response_time']) ? $service_performance['response_time'] : 0, 1),
    'staff_courtesy' => round(isset($service_performance['staff_courtesy']) ? $service_performance['staff_courtesy'] : 0, 1),
    'process_efficiency' => round(isset($service_performance['process_efficiency']) ? $service_performance['process_efficiency'] : 0, 1)
];
$stmt->close();

// 6. Satisfaction Trends
$query = "SELECT DATE_FORMAT(date_submitted, '%Y-%m-%d') as date, AVG((service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4) as avg_rating 
          FROM tbl_feedback_submissions WHERE date_submitted BETWEEN ? AND ? GROUP BY DATE(date_submitted) ORDER BY DATE(date_submitted)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();
$trends = [];
while($row = $result->fetch_assoc()) {
    $trends[$row['date']] = round($row['avg_rating'], 2);
}

// Fill in any missing days in the range with 0 for a continuous chart line
$period = new DatePeriod(new DateTime($startDate), new DateInterval('P1D'), (new DateTime($endDate))->modify('+1 day'));
$data['trends_labels'] = [];
$data['trends_data'] = [];
foreach ($period as $date) {
    $dateString = $date->format('Y-m-d');
    $dayAbbr = $date->format('M j');
    $data['trends_labels'][] = $dayAbbr;
    // Use isset() for older PHP compatibility, though '??' is fine in PHP 8.2
    $data['trends_data'][] = isset($trends[$dateString]) ? $trends[$dateString] : 0;
}
$stmt->close();


// --- OUTPUT FINAL JSON ---
echo json_encode($data);

// Close the connection
$conn->close();
?>
