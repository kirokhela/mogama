<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// Database configuration
$servername = "193.203.168.53";
$username = "u968010081_mogamaa";
$password = "Mogamaa_2000";
$dbname = "u968010081_mogamaa";

// Response function
function sendResponse($success, $message, $has_updates = false, $new_timestamp = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'has_updates' => $has_updates,
        'new_timestamp' => $new_timestamp
    ]);
    exit;
}

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        sendResponse(false, "Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    
    // Get input data
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['last_update'])) {
        sendResponse(false, "Invalid request data");
    }
    
    $client_last_update = intval($input['last_update']);
    
    // Get the latest timestamp from both tables
    $sql = "SELECT MAX(GREATEST(
        COALESCE((SELECT MAX(UNIX_TIMESTAMP(Timestamp)) FROM employees), 0),
        COALESCE((SELECT MAX(UNIX_TIMESTAMP(attendance_time)) FROM Attended_employee), 0)
    )) as latest_update";
    
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        $server_last_update = intval($row['latest_update']);
        
        // Check if there are updates
        $has_updates = ($server_last_update > $client_last_update);
        
        sendResponse(true, "Check completed", $has_updates, $server_last_update);
    } else {
        sendResponse(false, "Failed to check for updates: " . $conn->error);
    }
    
} catch (Exception $e) {
    sendResponse(false, "An error occurred: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>