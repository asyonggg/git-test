<?php
session_start(); // Must be the very first thing

// 1. Load Composer's autoloader and .env file
require __DIR__ . '/vendor/autoload.php';

$envPath = dirname(__DIR__); // This is the directory containing your .env file (e.g., P2/)
try {
    // createImmutable expects the DIRECTORY path, not the file path.
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load(); // Loads variables into $_ENV, $_SERVER, and via putenv()
} catch (Exception $e) {
    // Catch any Dotenv loading errors (e.g., file not found, parsing issues)
    error_log("Dotenv loading error: " . $e->getMessage()); // Log the error
    die("Critical Error: Could not load application configuration. Please check server logs."); // User-friendly message
}

// 2. Helper function to reliably get environment variables
function get_env_var(string $key, $default = null) {
    if (isset($_ENV[$key]) && $_ENV[$key] !== '') { // Check $_ENV first and ensure it's not an empty string
        return $_ENV[$key];
    }
    // Fallback to $_SERVER if not in $_ENV or if $_ENV value was empty
    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return $_SERVER[$key];
    }
    // Last resort: try getenv(), though it wasn't working reliably for you
    $value = getenv($key);
    return ($value !== false && $value !== '') ? $value : $default;
}

// 3. Fetch Configuration from Environment Variables
$client_id = get_env_var('GOOGLE_CLIENT_ID');
$client_secret = get_env_var('GOOGLE_CLIENT_SECRET');
$redirect_uri = get_env_var('GOOGLE_REDIRECT_URI');
$user_roles_json_string = get_env_var('USER_ROLES_JSON');

// 4. Validate Essential Configuration
if (empty($client_id) || empty($client_secret) || empty($redirect_uri)) {
    error_log("OAuth configuration (Client ID, Secret, or Redirect URI) is missing or empty. Check .env file.");
    die("Error: Application OAuth configuration is incomplete. Please contact support.");
}

if (empty($user_roles_json_string)) {
    error_log("USER_ROLES_JSON is not defined or empty in environment variables. Check .env file.");
    die("Error: User roles configuration is missing. Please contact support.");
}

// 5. Decode User Roles Configuration
$user_roles_config = json_decode($user_roles_json_string, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("Error decoding USER_ROLES_JSON: " . json_last_error_msg() . ". Original JSON: " . $user_roles_json_string);
    die("Error: User roles configuration is invalid. Please contact support.");
}

// --- Google OAuth Flow ---

// 6. If no authorization code is present, redirect to Google for authentication
if (!isset($_GET['code'])) {
    $auth_url_params = [
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'response_type' => 'code',
        'scope' => 'email profile', // Request email and basic profile
        'access_type' => 'offline', // To get a refresh token (optional, but good practice)
    ];
    $auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($auth_url_params);
    header("Location: " . $auth_url);
    exit;
}

// 7. Exchange authorization code for an access token
$token_request_data = [
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code',
    'code' => $_GET['code']
];

$ch_token = curl_init();
curl_setopt($ch_token, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
curl_setopt($ch_token, CURLOPT_POST, true);
curl_setopt($ch_token, CURLOPT_POSTFIELDS, http_build_query($token_request_data));
curl_setopt($ch_token, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_token, CURLOPT_CONNECTTIMEOUT, 10); // Increased timeout
curl_setopt($ch_token, CURLOPT_TIMEOUT, 20);      // Increased timeout
$token_response = curl_exec($ch_token);
$http_code_token = curl_getinfo($ch_token, CURLINFO_HTTP_CODE);

if (curl_errno($ch_token)) {
    error_log('cURL Error (token exchange): ' . curl_error($ch_token));
    curl_close($ch_token);
    die('Error communicating with Google (token). Please try again later.');
}
curl_close($ch_token);

if ($http_code_token !== 200) {
    error_log('Google OAuth Token Error (HTTP ' . $http_code_token . '): ' . $token_response);
    die('Error obtaining access token from Google. Response: ' . htmlspecialchars($token_response));
}

$token_data = json_decode($token_response, true);
if (!isset($token_data['access_token'])) {
    error_log('Access token not found in Google\'s response: ' . $token_response);
    die('Error: Access token not received from Google.');
}

// 8. Use access token to fetch user's profile information
$ch_user = curl_init();
curl_setopt($ch_user, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v3/userinfo');
curl_setopt($ch_user, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch_user, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token_data['access_token']]);
curl_setopt($ch_user, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch_user, CURLOPT_TIMEOUT, 20);
$user_response = curl_exec($ch_user);
$http_code_user = curl_getinfo($ch_user, CURLINFO_HTTP_CODE);

if (curl_errno($ch_user)) {
    error_log('cURL Error (userinfo): ' . curl_error($ch_user));
    curl_close($ch_user);
    die('Error communicating with Google (userinfo). Please try again later.');
}
curl_close($ch_user);

if ($http_code_user !== 200) {
    error_log('Google UserInfo Error (HTTP ' . $http_code_user . '): ' . $user_response);
    die('Error fetching user information from Google. Response: ' . htmlspecialchars($user_response));
}

$user_info = json_decode($user_response, true);
if (!isset($user_info['email'])) {
    error_log('Email not found in Google userinfo response: ' . $user_response);
    die('Error: Email not found in user profile from Google.');
}

// 9. Determine user role and store session data
$user_email_lower = strtolower(trim($user_info['email'])); // Trim and lowercase for robust matching
$authenticated_user_details = null;

// It's good practice to ensure keys in $user_roles_config are also consistently cased (e.g., all lowercase)
// For now, we assume $user_roles_json_string was generated with lowercase email keys.
if (array_key_exists($user_email_lower, $user_roles_config)) {
    $authenticated_user_details = $user_roles_config[$user_email_lower];
}

if (!$authenticated_user_details || !isset($authenticated_user_details['role'])) {
    error_log("Unauthorized access attempt or role misconfiguration for email: " . $user_info['email'] . ". User not found in configured roles or role attribute missing.");
    // Redirect to a generic login/error page
    header('Location: index.html?error=unauthorized_or_misconfigured');
    exit;
}

// 10. Store user information in session
$_SESSION['user_data'] = [
    'email' => $user_info['email'], // Store original case email if preferred for display
    'name' => isset($user_info['name']) ? $user_info['name'] : (isset($user_info['given_name']) ? $user_info['given_name'] : 'User'),
    'picture' => isset($user_info['picture']) ? $user_info['picture'] : null,
    'role' => $authenticated_user_details['role']
];

// Add office_name to session if the user is an office_head
if ($authenticated_user_details['role'] === 'office_head' && isset($authenticated_user_details['office_name'])) {
    $_SESSION['user_data']['office_name'] = $authenticated_user_details['office_name'];
}

// Optional: Store access token if you need to make further API calls on behalf of the user
// $_SESSION['access_token'] = $token_data['access_token'];
// If you store the refresh token (if `access_type=offline` was used and Google returned one), handle it securely.
// if (isset($token_data['refresh_token'])) { $_SESSION['refresh_token'] = $token_data['refresh_token']; }


// 11. Redirect based on role
if ($authenticated_user_details['role'] === 'admin') {
    header('Location: dashboard.php'); // Consistent naming convention
    exit;
} elseif ($authenticated_user_details['role'] === 'office_head') {
    header('Location: office_head_dashboard.php');
    exit;
} else {
    // This case should ideally be caught by the !$authenticated_user_details check above,
    // but as a fallback for unknown valid roles defined in config but not handled in redirect logic.
    error_log("Unknown but valid role encountered after authentication: " . $user_info['email'] . " Role: " . $authenticated_user_details['role']);
    header('Location: index.html?error=unknown_role'); // Or a generic dashboard
    exit;
}
?>