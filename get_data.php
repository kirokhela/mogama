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
function sendResponse($success, $message, $employees = [], $attended = []) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'employees' => $employees,
        'attended' => $attended
    ]);
    exit;
}

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        sendResponse(false, "Database connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    // Fetch employees (not yet attended)
    $employees_result = $conn->query("SELECT * FROM employees WHERE scan_count = 0 ORDER BY team, name");
    $employees = [];
    if ($employees_result) {
        $employees = $employees_result->fetch_all(MYSQLI_ASSOC);
    }
    
    // Fetch attended employees
    $attended_result = $conn->query("SELECT * FROM Attended_employee ORDER BY attendance_time DESC");
    $attended = [];
    if ($attended_result) {
        $attended = $attended_result->fetch_all(MYSQLI_ASSOC);
    }
    
    sendResponse(true, "Data retrieved successfully", $employees, $attended);
    
} catch (Exception $e) {
    sendResponse(false, "An error occurred: " . $e->getMessage());
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>