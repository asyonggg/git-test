<?php
// This is a RESTful API endpoint for handling Google OAuth authentication.
// It returns JSON responses instead of redirecting the user.

header('Content-Type: application/json');
session_start();

// --- SETUP AND CONFIGURATION ---

// Turn on error reporting for debugging. Turn this off for a live production server.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to send a structured JSON error and stop execution.
function send_json_error(int $statusCode, string $message, ?string $log_message = null) {
    http_response_code($statusCode);
    if ($log_message) {
        error_log($log_message);
    }
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

// 1. Define the project root and load Composer's autoloader
$projectRoot = dirname(__DIR__);
$vendorPath = $projectRoot . '/vendor/autoload.php';

if (!file_exists($vendorPath)) {
    send_json_error(500, 'Server configuration error.', 'Composer autoload file not found at expected path: ' . $vendorPath);
}
require $vendorPath;

// 2. Load the .env file for Google credentials
try {
    $dotenv = Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->load();
} catch (Exception $e) {
    send_json_error(500, 'Could not load application configuration.', 'Dotenv loading error: ' . $e->getMessage());
}

// 3. Helper function to get environment variables
function get_env_var(string $key, $default = null) {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: $default;
}

// 4. Fetch and Validate Configuration
$client_id = get_env_var('GOOGLE_CLIENT_ID');
$client_secret = get_env_var('GOOGLE_CLIENT_SECRET');
$redirect_uri = get_env_var('GOOGLE_REDIRECT_URI'); 

if (empty($client_id) || empty($client_secret) || empty($redirect_uri)) {
    send_json_error(500, 'Application configuration is incomplete.', 'Google OAuth credentials are missing from .env file.');
}

// --- DATABASE CONNECTION FOR ROLE CHECKING ---
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "db_jru_pulse";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    send_json_error(500, 'Database service is unavailable.', 'Auth DB Connection Failed: ' . $conn->connect_error);
}

// --- API ROUTING ---
$action = $_GET['action'] ?? null;

if ($action === 'get_auth_url') {
    // This action provides the URL for the "Login with Google" button
    $auth_url_params = [
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'offline',
    ];
    $auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($auth_url_params);
    
    echo json_encode(['success' => true, 'auth_url' => $auth_url]);
    exit;
}

if (isset($_GET['code'])) {
    // This block handles the callback from Google, exchanging the code for user data
    $code = $_GET['code'];

    // Exchange authorization code for an access token
    $token_request_data = [ 'client_id' => $client_id, 'client_secret' => $client_secret, 'redirect_uri' => $redirect_uri, 'grant_type' => 'authorization_code', 'code' => $code ];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_request_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $token_response = curl_exec($ch);
    
    if (curl_errno($ch)) { $curl_error_msg = curl_error($ch); curl_close($ch); send_json_error(500, 'Error communicating with Google.', 'cURL Error (token): ' . $curl_error_msg); }
    $token_data = json_decode($token_response, true);
    if (!isset($token_data['access_token'])) { curl_close($ch); send_json_error(400, 'Could not obtain access token from Google.', 'Google OAuth Token Error: ' . $token_response); }
    curl_close($ch);

    // Use access token to fetch user's profile information
    $ch_user = curl_init();
    curl_setopt($ch_user, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
    curl_setopt($ch_user, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch_user, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token_data['access_token']]);
    $user_response = curl_exec($ch_user);

    if (curl_errno($ch_user)) { $curl_error_msg = curl_error($ch_user); curl_close($ch_user); send_json_error(500, 'Error communicating with Google.', 'cURL Error (userinfo): ' . $curl_error_msg); }
    curl_close($ch_user);
    
    $user_info = json_decode($user_response, true);
    if (!isset($user_info['email'])) { send_json_error(400, 'Could not retrieve user email from Google.', 'Email not found in userinfo response: ' . $user_response); }

    // --- NEW: AUTHENTICATE AGAINST THE DATABASE ---
    $user_email = $user_info['email'];
    $stmt = $conn->prepare("SELECT role, office_name FROM tbl_users WHERE email = ?");
    $stmt->bind_param("s", $user_email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        send_json_error(403, 'Access denied. Your email is not registered for this application.', "Unauthorized access attempt for email: " . $user_email);
    }
    
    $user_details = $result->fetch_assoc();
    $stmt->close();

    // Update user's name and picture from Google
    $update_stmt = $conn->prepare("UPDATE tbl_users SET display_name = ?, picture_url = ? WHERE email = ?");
    $user_name = $user_info['name'] ?? 'User';
    $user_picture = $user_info['picture'] ?? null;
    $update_stmt->bind_param("sss", $user_name, $user_picture, $user_email);
    $update_stmt->execute();
    $update_stmt->close();

    // Set Session Data
    $_SESSION['user_data'] = [
        'email' => $user_email,
        'name' => $user_name,
        'picture' => $user_picture,
        'role' => $user_details['role']
    ];

    $redirect_url = 'index.html?error=unknown_role'; // Default fallback
    if ($user_details['role'] === 'admin') {
        $redirect_url = 'dashboard.php';
    } elseif ($user_details['role'] === 'office_head') {
        $_SESSION['user_data']['office_name'] = $user_details['office_name'] ?? null;
        $redirect_url = 'office_head_dashboard.php';
    }
    
    $conn->close();

    // Return success response with user data and where to redirect
    echo json_encode(['success' => true, 'message' => 'Authentication successful.', 'user' => $_SESSION['user_data'], 'redirect_url' => $redirect_url]);
    exit;
}

// Fallback for invalid requests to the API
$conn->close();
send_json_error(400, 'Invalid request. Please specify an action.');

?>
