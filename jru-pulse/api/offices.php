<?php

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
// 1. FIXED THE TYPO: Heaaders -> Headers
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// This handles the browser's preflight "OPTIONS" request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once '../config/connection.php';


function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data
    ]);
    exit;
}

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    respond(false, "Database connection failed", null, 500);
}


$method = $_SERVER["REQUEST_METHOD"];

switch($method) {
    case "GET":
        try {
            $query = "SELECT * FROM offices WHERE is_active = 1 ORDER BY name";
            $stmt =  $db->prepare($query);
            $stmt->execute();

            $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

            respond(true, "Offices retrieved successfully", $offices);
        } catch(PDOException $e) {
            respond(false, "Error retrieving offices: " . $e->getMessage(), null, 500);
        }
        break;
    
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);

        if(!isset($data["name"]) || empty($data["name"]) || !isset($data["code"]) || empty($data["code"])) {
            respond(false, "Name and code are required", null, 400);
        }

        try {
            $query = "INSERT INTO offices (name, code, description)
                      VALUES(:name, :code, :description)";
            $stmt = $db->prepare($query);

            // Sanitize and prepare variables for binding
            $name = htmlspecialchars(strip_tags($data['name']));
            $code = htmlspecialchars(strip_tags($data['code']));
            $description = isset($data['description']) ? htmlspecialchars(strip_tags($data['description'])) : '';

            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":code", $code);
            $stmt->bindParam(":description", $description);

            $stmt->execute();

            // 2. FIXED THE UNDEFINED VARIABLE: Get the ID of the new row
            $id = $db->lastInsertId();
            
            respond(true, "Office created successfully", ["id" => $id]);

        } catch(PDOException $e) {
            respond(false, "Error creating office: " . $e->getMessage(), null, 500);
        }
        break;
    
    default:
        respond(false, "Method not allowed", null, 405);
}

?>