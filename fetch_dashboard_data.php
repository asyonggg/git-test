<?php
session_start();
header('Content-Type: application/json');
error_reporting(0); // Disable error reporting for production

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    echo json_encode(['error' => 'Authentication Required']);
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

// --- CALCULATE DATE RANGE BASED ON FILTER ---
// Set default period to 'this_week'
$period = isset($_GET['period']) ? $_GET['period'] : 'this_week';

if (isset($_GET['startDate']) && isset($_GET['endDate'])) {
    // Use custom date range if provided
    $startDate = $_GET['startDate'];
    $endDate = $_GET['endDate'];
} else {
    // Calculate date range based on the period preset
    $endDate = date('Y-m-d');
    switch ($period) {
        case 'this_week':
            // Note: 'N' gives day of week where 1 is Monday.
            $dayOfWeek = date('N');
            $startDate = date('Y-m-d', strtotime("-" . ($dayOfWeek - 1) . " days"));
            break;
        case 'this_month':
            $startDate = date('Y-m-01');
            break;
        case 'this_quarter':
            $currentMonth = date('n');
            $currentYear = date('Y');
            if ($currentMonth >= 1 && $currentMonth <= 3) {
                $startDate = $currentYear . '-01-01';
            } elseif ($currentMonth >= 4 && $currentMonth <= 6) {
                $startDate = $currentYear . '-04-01';
            } elseif ($currentMonth >= 7 && $currentMonth <= 9) {
                $startDate = $currentYear . '-07-01';
            } else {
                $startDate = $currentYear . '-10-01';
            }
            break;
        case 'this_year':
            $startDate = date('Y-01-01');
            break;
        case 'all_time':
        default:
            // For 'all_time', find the earliest and latest dates in the table
            $date_range_result = $conn->query("SELECT MIN(date_submitted) as min_date, MAX(date_submitted) as max_date FROM tbl_feedback_submissions");
            $date_range = $date_range_result->fetch_assoc();
            $startDate = $date_range['min_date'] ? $date_range['min_date'] : date('Y-m-d');
            $endDate = $date_range['max_date'] ? $date_range['max_date'] : date('Y-m-d');
            break;
    }
}


// --- REST OF THE SCRIPT (REMAINS THE SAME) ---

$data = [];

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
    'service_quality' => round($service_performance['service_quality'] ?? 0, 1),
    'response_time' => round($service_performance['response_time'] ?? 0, 1),
    'staff_courtesy' => round($service_performance['staff_courtesy'] ?? 0, 1),
    'process_efficiency' => round($service_performance['process_efficiency'] ?? 0, 1)
];
$stmt->close();

// 6. Satisfaction Trends
// The logic for fetching and formatting trends data remains the same...
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
    $data['trends_data'][] = $trends[$dateString] ?? 0;
}
$stmt->close();

// Pass back the calculated date range for display in the input fields
$data['startDate'] = $startDate;
$data['endDate'] = $endDate;

echo json_encode($data);

$conn->close();
?>
