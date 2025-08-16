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
function sendResponse($success, $message, $type = 'info') {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'type' => $type
    ]);
    exit;
}

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        sendResponse(false, "Database connection failed: " . $conn->connect_error, 'danger');
    }
    
    // Set charset to utf8
    $conn->set_charset("utf8");
    
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        sendResponse(false, "Invalid JSON data received", 'danger');
    }
    
    // Validate required fields
    $required_fields = ['id', 'name', 'payment_amount', 'team'];
    foreach ($required_fields as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            sendResponse(false, "Missing required field: $field", 'danger');
        }
    }
    
    $id = trim($input['id']);
    $name = trim($input['name']);
    $payment_amount = trim($input['payment_amount']);
    $team = trim($input['team']);
    
    // Step 1: Check if employee exists in employees table
    $check_employee_stmt = $conn->prepare("SELECT id FROM employees WHERE id = ?");
    if (!$check_employee_stmt) {
        sendResponse(false, "Database error: " . $conn->error, 'danger');
    }
    
    $check_employee_stmt->bind_param("s", $id);
    $check_employee_stmt->execute();
    $employee_result = $check_employee_stmt->get_result();
    
    if ($employee_result->num_rows === 0) {
        $check_employee_stmt->close();
        sendResponse(false, "This person is not in our database", 'warning');
    }
    
    $check_employee_stmt->close();
    
    // Step 2: Check if employee is already in Attended_employee table
    $check_attended_stmt = $conn->prepare("SELECT id FROM Attended_employee WHERE id = ?");
    if (!$check_attended_stmt) {
        sendResponse(false, "Database error: " . $conn->error, 'danger');
    }
    
    $check_attended_stmt->bind_param("s", $id);
    $check_attended_stmt->execute();
    $attended_result = $check_attended_stmt->get_result();
    
    if ($attended_result->num_rows > 0) {
        $check_attended_stmt->close();
        sendResponse(false, "Sorry, this QR code is already scanned", 'warning');
    }
    
    $check_attended_stmt->close();
    
    // Step 3: Insert into Attended_employee table
    $insert_stmt = $conn->prepare("INSERT INTO Attended_employee (id, name, payment_amount, team) VALUES (?, ?, ?, ?)");
    if (!$insert_stmt) {
        sendResponse(false, "Database error: " . $conn->error, 'danger');
    }
    
    $insert_stmt->bind_param("ssds", $id, $name, $payment_amount, $team);
    
    if ($insert_stmt->execute()) {
        $insert_stmt->close();
        sendResponse(true, "Attendance marked successfully! Welcome, $name.", 'success');
    } else {
        $insert_stmt->close();
        sendResponse(false, "Failed to mark attendance: " . $conn->error, 'danger');
    }
    
} catch (Exception $e) {
    sendResponse(false, "An error occurred: " . $e->getMessage(), 'danger');
} finally {
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
}
?>