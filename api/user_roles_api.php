<?php
// This API provides CRUD (Create, Read, Update, Delete) functionality for user roles.
// It will be used by the user_management.php page.

header('Content-Type: application/json');
session_start();

// --- AUTHENTICATION & AUTHORIZATION ---
// Only allow admins to access this sensitive API
if (!isset($_SESSION['user_data']) || $_SESSION['user_data']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden: You do not have permission to perform this action.']);
    exit;
}

// --- DATABASE CONNECTION ---
$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "db_jru_pulse";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        // Get all users
        $result = $conn->query("SELECT id, email, display_name, role, office_name FROM tbl_users ORDER BY email ASC");
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode(['success' => true, 'users' => $users]);
        break;

    case 'POST':
        // Add a new user
        $email = $input['email'] ?? null;
        $role = $input['role'] ?? null;
        $office_name = $input['office_name'] ?? null;

        if (empty($email) || empty($role)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Email and role are required.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tbl_users (email, role, office_name) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $role, $office_name);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User added successfully.']);
        } else {
            http_response_code(409); // Conflict, likely a duplicate email
            echo json_encode(['success' => false, 'error' => 'Failed to add user. Email may already exist.']);
        }
        $stmt->close();
        break;

    case 'PUT':
        // Update a user's role or office
        $id = $input['id'] ?? null;
        $role = $input['role'] ?? null;
        $office_name = $input['office_name'] ?? null;

        if (empty($id) || empty($role)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID and role are required.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tbl_users SET role = ?, office_name = ? WHERE id = ?");
        $stmt->bind_param("ssi", $role, $office_name, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update user.']);
        }
        $stmt->close();
        break;

    case 'DELETE':
        // Delete a user
        $id = $input['id'] ?? null;

        if (empty($id)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'User ID is required.']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM tbl_users WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to delete user.']);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
        break;
}

$conn->close();
?>
