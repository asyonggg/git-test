<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../config/connection.php';
function respond($s, $m, $d = null, $c = 200) { http_response_code($c); echo json_encode(["success"=>(bool)$s, "message"=>$m, "data"=>$d]); exit; }

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    respond(false, "DB Connection Failed", null, 500);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    // Basic validation
    if (empty($data['survey_id']) || empty($data['respondent']) || empty($data['answers'])) {
        respond(false, "Missing required data.", null, 400);
    }

    $db->beginTransaction(); // Start a transaction

    try {
        // --- Step 1: Find or Create the Respondent ---
        $respondent_info = $data['respondent'];
        $identifier = $respondent_info['identifier'];

        // Check if this respondent already exists
        $stmt = $db->prepare("SELECT id FROM respondents WHERE identifier = :identifier");
        $stmt->bindValue(':identifier', $identifier);
        $stmt->execute();
        $respondent_id = $stmt->fetchColumn();

        if (!$respondent_id) {
            // Respondent does not exist, so create them
            $query = "INSERT INTO respondents (respondent_type, identifier, division, course) VALUES (:type, :identifier, :division, :course)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':type', $respondent_info['type']);
            $stmt->bindValue(':identifier', $identifier);
            $stmt->bindValue(':division', $respondent_info['division'] ?? null);
            $stmt->bindValue(':course', $respondent_info['course'] ?? null);
            $stmt->execute();
            $respondent_id = $db->lastInsertId();
        }

        // --- Step 2: Save the Survey Response ---
        $query = "INSERT INTO survey_responses (survey_id, respondent_id, answers_json) VALUES (:survey_id, :respondent_id, :answers_json)";
        $stmt = $db->prepare($query);
        
        $answers_json = json_encode($data['answers']); // Convert answers array to JSON string

        $stmt->bindValue(':survey_id', $data['survey_id']);
        $stmt->bindValue(':respondent_id', $respondent_id);
        $stmt->bindValue(':answers_json', $answers_json);
        $stmt->execute();

        $db->commit(); // If everything was successful, commit the changes
        respond(true, "Thank you! Your feedback has been submitted.");

    } catch (PDOException $e) {
        $db->rollBack(); // If any error occurred, undo all changes
        respond(false, "Database Error: " . $e->getMessage(), null, 500);
    }
} else {
    respond(false, "Method Not Allowed", null, 405);
}
?>