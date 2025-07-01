<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  http_response_code(200);
  exit();
}

require_once '../config/connection.php';

// Use the same respond function for consistent API responses
function respond($success, $message, $data = null, $code = 200) {
    http_response_code($code);
    echo json_encode([
        "success" => (bool)$success,
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

$method = $_SERVER['REQUEST_METHOD'];

error_log("API a/surveys.php received a request with method: " . $method);

switch($method) {
   case 'GET':
        try {
            // Check if the request is for a SINGLE survey 
            if (isset($_GET['id']) && is_numeric($_GET['id'])) {
                
            //If an ID is provided, fetch that specific survey
                $id = intval($_GET['id']);
                $query = "SELECT s.*, o.name as office_name, se.name as service_name 
                        FROM surveys s
                        LEFT JOIN offices o ON s.office_id = o.id
                        LEFT JOIN services se ON s.service_id = se.id
                        WHERE s.id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':id', $id); // Use bindValue for safety
                $stmt->execute();
                
                $survey = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($survey) {
                    // Decode the JSON string before sending it back
                    $survey['questions_json'] = json_decode($survey['questions_json']);
                    respond(true, "Survey retrieved successfully.", $survey);
                } else {
                    respond(false, "Survey not found.", null, 404);
                }

            } else {
                //  If no ID, then it's a request for a LIST of surveys, check if we want to show archived surveys
                if (isset($_GET['show_archived']) && $_GET['show_archived'] == 'true') {
                    // Get ARCHIVED surveys
                    $query = "SELECT s.*, o.name as office_name, se.name as service_name 
                            FROM surveys s
                            LEFT JOIN offices o ON s.office_id = o.id
                            LEFT JOIN services se ON s.service_id = se.id
                            WHERE s.status = 'archived'
                            ORDER BY s.updated_at DESC";
                } else {
                    // Get ACTIVE and DRAFT surveys
                    $query = "SELECT s.*, o.name as office_name, se.name as service_name 
                            FROM surveys s
                            LEFT JOIN offices o ON s.office_id = o.id
                            LEFT JOIN services se ON s.service_id = se.id
                            WHERE s.status IN ('draft', 'active')
                            ORDER BY s.created_at DESC";
                }
                
                $stmt = $db->prepare($query);
                $stmt->execute();
                $surveys = $stmt->fetchAll(PDO::FETCH_ASSOC);
                respond(true, "Surveys retrieved successfully.", $surveys);
            }

        } catch (PDOException $e) {
            respond(false, "Database error retrieving surveys: " . $e->getMessage(), null, 500);
        }
        break;

    case 'POST':
        // handle the creation of survey, Get the data sent from the survey builder's JavaScript
        $data = json_decode(file_get_contents("php://input"), true);

        // Basic validation
        if (empty($data['title']) || empty($data['office_id']) || empty($data['service_id'])) {
            respond(false, "Title, office, and service are required.", null, 400);
        }

        try {
            $query = "INSERT INTO surveys (title, description, office_id, service_id, status, questions_json) 
                      VALUES (:title, :description, :office_id, :service_id, :status, :questions_json)";
            
            $stmt = $db->prepare($query);

            // the questions_json is expected to be an array of questions, so we convert it to JSON
            $questions_json = json_encode($data['questions']);

            // Bind all the parameters
            $stmt->bindValue(':title', $data['title']);
            $stmt->bindValue(':description', $data['description'] ?? '');
            $stmt->bindValue(':office_id', $data['office_id']);
            $stmt->bindValue(':service_id', $data['service_id']);
            $stmt->bindValue(':status', $data['status'] ?? 'draft');
            $stmt->bindValue(':questions_json', $questions_json);
            
            $stmt->execute();
            
            // Get the ID of the new survey we just created
            $newSurveyId = $db->lastInsertId();
            
            respond(true, "Survey saved successfully.", ["id" => $newSurveyId]);

        } catch (PDOException $e) {
            respond(false, "Database error: " . $e->getMessage(), null, 500);
        }
    break;
    
    case 'PUT':
        // The ID from the URL is always required for any PUT action.
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            respond(false, "A valid Survey ID is required.", null, 400);
        }
        $id = intval($_GET['id']);
        
        // Get the data from the request body.
        $data = json_decode(file_get_contents("php://input"), true);
        // Determine the action. If no action is specified, it's a standard update.
        $action = $data['action'] ?? 'update_details'; 

        try {
            if ($action == 'publish') {
                // --- PUBLISH LOGIC ---
                $query = "UPDATE surveys SET status = 'active' WHERE id = :id AND status = 'draft'";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    respond(true, "Survey published successfully.");
                } else {
                    respond(false, "Survey could not be published (already active or archived).", null, 409);
                }

            } elseif ($action == 'reactivate') {
                // --- REACTIVATE LOGIC ---
                $query = "UPDATE surveys SET status = 'draft' WHERE id = :id AND status = 'archived'";
                $stmt = $db->prepare($query);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                if ($stmt->rowCount() > 0) {
                    respond(true, "Survey reactivated successfully.");
                } else {
                    respond(false, "Survey could not be reactivated.", null, 409);
                }

            } elseif ($action == 'update_details') {
            //Logic for survey details update
                if (empty($data['title']) || empty($data['office_id']) || empty($data['service_id'])) {
                    respond(false, "Title, office, and service are required for update.", null, 400);
                }
                $query = "UPDATE surveys SET title = :title, office_id = :office_id, service_id = :service_id, questions_json = :questions_json WHERE id = :id";
                $stmt = $db->prepare($query);
                $questions_json = json_encode($data['questions']);
                $stmt->bindValue(':title', $data['title']);
                $stmt->bindValue(':office_id', $data['office_id']);
                $stmt->bindValue(':service_id', $data['service_id']);
                $stmt->bindValue(':questions_json', $questions_json);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                respond(true, "Survey draft updated successfully.");
                
            } else {
                // If the action is something unknown
                respond(false, "Invalid action specified.", null, 400);
            }
        } catch (PDOException $e) {
            respond(false, "Database error during update: " . $e->getMessage(), null, 500);
        }
    break;

    case 'DELETE':
        //Handlde soft deletion or archiving a surey
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            respond(false, "A valid Survey is required for archival.", null, 400);
        }

        $id = intval($_GET['id']);

        try {
            //Soft deletion by chaing the status to archived
            $query = "UPDATE surveys SET status = 'archived' WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':id', $id);

            if ($stmt->execute()) {
                //check if any row was affected to cofirm if the survey existed
                if ($stmt->rowCount() > 0) {
                    respond(true, "Survey archived successfully.");
                } else {
                    respond(false, "Survey not found or already archived.", null, 404);
                }
            } else {
                respond(false, "Failed to archive survey. ");
            }
        } catch (PDOException $e) {
            respond(false, "Database error while archiving survey: " . $e->getMessage(), null, 500);
        }
    break;

    default:
    error_log("Fell into the DEFAULT case. Method was: " . $method); // This will tell us if we missed the POST case
    respond(false, "Method not allowed", null, 405);
}
?>