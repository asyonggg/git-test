<?php

header("Access-Control-Allow-Origin: *"); // Or specify your frontend domain
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
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
            $query = "SELECT * FROM offices WHERE is_active = 1";
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

    case "PUT":
                   // Get ID from URL query string (e.g., /api/offices.php?id=5)
            if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                respond(false, "A valid Office ID is required for update.", null, 400);
            }
            $id = intval($_GET['id']);

            // Get the data from the request body
            $data = json_decode(file_get_contents("php://input"), true);


           if (!isset($data["name"]) || empty($data["name"]) || !isset($data["code"]) || empty($data["code"])) {
                respond(false, "Name and code are required.", null, 400);
            }

            try{
                $query = "UPDATE offices 
                  SET name = :name, code = :code, description = :description 
                  WHERE id = :id"; //
                 $stmt = $db->prepare($query);

                // Sanitize and prepare variables for binding
                $name = htmlspecialchars(strip_tags($data['name']));
                $code = htmlspecialchars(strip_tags($data['code']));
                $description = isset($data['description']) ? htmlspecialchars(strip_tags($data['description'])) : '';
               

                $stmt->bindParam(":name", $name);
                $stmt->bindParam(":code", $code);
                $stmt->bindParam(":description", $description);
                $stmt->bindParam(":id", $id); 

                if ($stmt->execute()) {
                    respond(true, "Office updated successfully");
                } else {
                   respond(false, "Failed to update office. The office may not exist.", null, 404);
                }
            } catch(PDOException $e) {
                respond(false, "Error updating office: " . $e->getMessage(), null, 500);
            }
            break;

    case "DELETE" :
        // Get ID from URL query string
         if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
                respond(false, "A valid Office ID is required for archival.", null, 400);
            }
              $id = intval($_GET['id']);

        try {
            // Note: A better practice is to "soft delete" by setting is_active = 0
            // But for simplicity, we will do a hard delete.
            $query = "UPDATE offices SET is_active = 0 WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id', $id);

            if($stmt->execute()) {
                // Check if any row was actually affected.
                if ($stmt->rowCount() > 0) {
                    respond(true, "Office archived successfully");
                    } else {
                    respond(false, "Office not found or already inactive.", null, 404);
                }
                 } else {
                respond(false, "Failed to archive office.", null, 500);
            }
                 } catch (PDOException $e) {
            // The foreign key error is less likely here, but it's good to keep
         if($e->getCode() == 23000){
                respond(false, "This office cannot be archived due to a database constraint.", null, 409);
            }
            respond(false, "Database error while archiving office: " . $e->getMessage(), null, 500);
        }
        break;
    
    default:
        respond(false, "Method not allowed", null, 405);
}

?>