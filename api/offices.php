<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Or specify your frontend domain
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Header: Content-Type, Authorization, X-Requested-With");

// This handles the browser's preflight "OPTIONS" request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once '../config/connection.php';


function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => (bool)$success, // forces the value to be a true boolean
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

//REST API for Offices

switch($method) {
    //Read Retrieve
    case "GET":
        try {
           if (isset($_GET['show_archived_offices']) && $_GET['show_archived_offices'] == 'true') {
                $query = "SELECT * FROM offices WHERE is_active = 0 ORDER BY name";
            } else {
                $query = "SELECT * FROM offices WHERE is_active = 1 ORDER BY name";
            }
            
            $stmt =  $db->prepare($query);
            $stmt->execute();

            $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

             error_log("Offices count: " . count($offices));
             error_log("Offices data: " . json_encode($offices));

            respond(true, "Offices retrieved successfully", $offices);
        
        } catch(PDOException $e) {
            respond(false, "Error retrieving offices: " . $e->getMessage(), null, 500);
        }
        break;
    
    case "POST":
    //Create
    $data = json_decode(file_get_contents("php://input"), true);

    if(!isset($data["name"]) || empty($data["name"]) || !isset($data["code"]) || empty($data["code"])) {
        respond(false, "Name and code are required", null, 400);
    }

    try {
        $query = "INSERT INTO offices (name, code, description)
                  VALUES(:name, :code, :description)";
        $stmt = $db->prepare($query);

        // Sanitize and prepare variables
        $name = htmlspecialchars(strip_tags($data['name']));
        $code = htmlspecialchars(strip_tags($data['code']));
        $description = isset($data['description']) ? htmlspecialchars(strip_tags($data['description'])) : '';

        // Use bindValue() for consistency and safety
        $stmt->bindValue(":name", $name);
        $stmt->bindValue(":code", $code);
        $stmt->bindValue(":description", $description);

        $stmt->execute();

        $id = $db->lastInsertId();
        
        respond(true, "Office created successfully", ["id" => $id]);

    } catch(PDOException $e) {
        // This catch block will tell us if it's a database error like a duplicate entry
        if ($e->getCode() == 23000) { // Code 23000 is for integrity constraint violations
            respond(false, "An office with this name or code already exists.", null, 409); // 409 Conflict
        }
        respond(false, "Error creating office: " . $e->getMessage(), null, 500);
    }
    break;

    case "PUT":
       

    case "DELETE" :
        // Get ID from URL query string
         if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                respond(false, "A valid Office ID is required for archival.", null, 400);
            }
              $id = intval($_GET['id']);

        try {
           //soft delete logic: gawing 0 yung is_active
            $query = "UPDATE offices SET is_active = 0 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);

               if($stmt->execute()) {
                    respond(true, "Office archived successfully");
                } else {
                    respond(false, "Failed to archive office.", null, 500);
                }

            } catch (PDOException $e) {
                respond(false, "Database error: " . $e->getMessage(), null, 500);
        }
        break;
    
    default:
        respond(false, "Method not allowed", null, 405);
}

?>