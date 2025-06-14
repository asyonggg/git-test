<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

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

// Get database connection
try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    respond(false, "Database connection failed", null, 500);
}

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        try {
            $office_id = isset($_GET['office_id']) ? $_GET['office_id'] : null;
            
            if ($office_id) {
                $query = "SELECT s.*, o.name as office_name FROM services s 
                         LEFT JOIN offices o ON s.office_id = o.id 
                         WHERE s.office_id = :office_id AND s.is_active = 1 
                         ORDER BY s.name";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':office_id', $office_id);
            } else {
                $query = "SELECT s.*, o.name as office_name FROM services s 
                         LEFT JOIN offices o ON s.office_id = o.id 
                         WHERE s.is_active = 1 
                         ORDER BY o.name, s.name";
                $stmt = $db->prepare($query);
            }
            
            $stmt->execute();
            
            $services = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $services[] = $row;
            }
            
            respond(true, "Services retrieved successfully", $services);
        } catch (PDOException $e) {
            respond(false, "Error retrieving services: " . $e->getMessage(), null, 500);
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!isset($data['office_id']) || !isset($data['name']) || !isset($data['code'])) {
            respond(false, "Office ID, name, and code are required", null, 400);
        }
        
        try {
            $query = "INSERT INTO services (office_id, name, code, description) 
                     VALUES (:office_id, :name, :code, :description)";
            $stmt = $db->prepare($query);
            
            $stmt->bindParam(':office_id', $data['office_id']);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':code', $data['code']);
            $stmt->bindParam(':description', $data['description'] ?? '');
            
            $stmt->execute();
            $id = $db->lastInsertId();
            
            respond(true, "Service created successfully", ["id" => $id]);
        } catch (PDOException $e) {
            respond(false, "Error creating service: " . $e->getMessage(), null, 500);
        }
        break;
        
    default:
        respond(false, "Method not allowed", null, 405);
}
?>
