<?php
session_start();
header('Content-Type: application/json');

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
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// --- LOGIC TO CHECK FOR PERFORMANCE DIPS (SIMULATES A DAILY CRON JOB) ---
function checkForWeeklyDips($conn) {
    // Only run this check once per day to avoid overhead.
    // In a real-world scenario, this would be a separate script run by a cron job.
    $today = date('Y-m-d');
    if (isset($_SESSION['last_dip_check']) && $_SESSION['last_dip_check'] == $today) {
        return; // Already checked today
    }

    $dip_threshold = 3.5; // The rating below which we consider a "dip"
    $last_week_start = date('Y-m-d', strtotime('-7 days'));
    $last_week_end = date('Y-m-d', strtotime('-1 day'));
    $previous_week_start = date('Y-m-d', strtotime('-14 days'));
    $previous_week_end = date('Y-m-d', strtotime('-8 days'));

    // Get all services that received feedback in the last week
    $services_query = "
        SELECT DISTINCT office, service 
        FROM tbl_feedback_submissions 
        WHERE date_submitted BETWEEN ? AND ?
    ";
    $stmt_services = $conn->prepare($services_query);
    $stmt_services->bind_param("ss", $last_week_start, $last_week_end);
    $stmt_services->execute();
    $services_result = $stmt_services->get_result();
    
    while ($service_row = $services_result->fetch_assoc()) {
        $office = $service_row['office'];
        $service = $service_row['service'];

        // Get average for the last 7 days
        $query_last_week = "SELECT AVG((service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4) as avg_rating FROM tbl_feedback_submissions WHERE office = ? AND service = ? AND date_submitted BETWEEN ? AND ?";
        $stmt_lw = $conn->prepare($query_last_week);
        $stmt_lw->bind_param("ssss", $office, $service, $last_week_start, $last_week_end);
        $stmt_lw->execute();
        $avg_last_week = $stmt_lw->get_result()->fetch_assoc()['avg_rating'];
        $stmt_lw->close();

        // Get average for the 7 days before that
        $query_prev_week = "SELECT AVG((service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4) as avg_rating FROM tbl_feedback_submissions WHERE office = ? AND service = ? AND date_submitted BETWEEN ? AND ?";
        $stmt_pw = $conn->prepare($query_prev_week);
        $stmt_pw->bind_param("ssss", $office, $service, $previous_week_start, $previous_week_end);
        $stmt_pw->execute();
        $avg_prev_week = $stmt_pw->get_result()->fetch_assoc()['avg_rating'];
        $stmt_pw->close();

        // If the service has a rating and it has dipped below the threshold
        if ($avg_last_week && $avg_last_week < $dip_threshold) {
             // To avoid spam, check if we already created an alert for this service in the last 7 days
            $check_alert_query = "SELECT id FROM tbl_weekly_alerts WHERE office = ? AND service = ? AND alert_date >= ?";
            $stmt_check = $conn->prepare($check_alert_query);
            $stmt_check->bind_param("sss", $office, $service, $last_week_start);
            $stmt_check->execute();
            if ($stmt_check->get_result()->num_rows == 0) {
                // No recent alert, so create a new one
                $insert_alert_query = "INSERT INTO tbl_weekly_alerts (office, service, avg_rating_this_week, avg_rating_last_week, alert_date) VALUES (?, ?, ?, ?, ?)";
                $stmt_insert = $conn->prepare($insert_alert_query);
                $rating_last_week_val = $avg_prev_week ? $avg_prev_week : 0; // Handle case with no previous data
                $stmt_insert->bind_param("ssdds", $office, $service, $avg_last_week, $rating_last_week_val, $today);
                $stmt_insert->execute();
                $stmt_insert->close();
            }
            $stmt_check->close();
        }
    }
    $stmt_services->close();
    $_SESSION['last_dip_check'] = $today; // Mark that we've checked for today
}

// --- LOGIC ROUTING ---
$action = $_GET['action'] ?? 'fetch';

if ($action === 'fetch') {
    // Run the daily check for performance dips
    checkForWeeklyDips($conn);
    
    $notifications = [];

    // 1. Fetch CRITICAL alerts (individual low scores)
    $critical_threshold = 2.5;
    $query_critical = "
        SELECT 
            'critical' as type, id, student_no, office, service, suggestions, date_submitted, time_submitted,
            (service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4 as avg_rating
        FROM tbl_feedback_submissions 
        WHERE (service_outcome + speed_of_service + processing_instruction + staff_professionalism_courtesy) / 4 <= ?
        AND is_viewed = 0
    ";
    $stmt_critical = $conn->prepare($query_critical);
    $stmt_critical->bind_param("d", $critical_threshold);
    $stmt_critical->execute();
    $result_critical = $stmt_critical->get_result();
    while ($row = $result_critical->fetch_assoc()) { $notifications[] = $row; }
    $stmt_critical->close();

    // 2. Fetch WARNING alerts (weekly dips)
    $query_warning = "SELECT 'warning' as type, id, office, service, avg_rating_this_week, avg_rating_last_week, alert_date FROM tbl_weekly_alerts WHERE is_viewed = 0";
    $result_warning = $conn->query($query_warning);
    while ($row = $result_warning->fetch_assoc()) { $notifications[] = $row; }

    // Sort all notifications by date (newest first)
    usort($notifications, function($a, $b) {
        $date_a = $a['type'] === 'critical' ? strtotime($a['date_submitted'] . ' ' . $a['time_submitted']) : strtotime($a['alert_date']);
        $date_b = $b['type'] === 'critical' ? strtotime($b['date_submitted'] . ' ' . $b['time_submitted']) : strtotime($b['alert_date']);
        return $date_b <=> $date_a;
    });

    echo json_encode(['notifications' => $notifications]);

} elseif (isset($_GET['id']) && isset($_GET['type'])) {
    // --- MARK A SPECIFIC NOTIFICATION AS READ ---
    $id = (int)$_GET['id'];
    $type = $_GET['type'];

    if ($type === 'critical') {
        $stmt = $conn->prepare("UPDATE tbl_feedback_submissions SET is_viewed = 1 WHERE id = ?");
    } elseif ($type === 'warning') {
        $stmt = $conn->prepare("UPDATE tbl_weekly_alerts SET is_viewed = 1 WHERE id = ?");
    }

    if (isset($stmt)) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Database update failed']);
        }
        $stmt->close();
    }
}

$conn->close();
?>
