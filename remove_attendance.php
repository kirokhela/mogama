<?php
header('Content-Type: application/json; charset=utf-8');
// It's better to restrict this to your actual domain in production instead of '*'
header('Access-Control-Allow-Origin: *'); 

// --- Database Connection ---
$servername = "193.203.168.53";
$username   = "u968010081_mogamaa";
$password   = "Mogamaa_2000";
$dbname     = "u968010081_mogamaa";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    // It's good practice to stop the script if the connection fails
    http_response_code(500 ); // Internal Server Error
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}
$conn->set_charset("utf8mb4");

// --- Validate Input ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405 ); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    http_response_code(400 ); // Bad Request
    echo json_encode(["success" => false, "message" => "Employee ID is required."]);
    exit;
}

// --- Start Transaction ---
$conn->begin_transaction();

try {
    // 1. Delete the record from the Attended_employee table
    $stmt_delete = $conn->prepare("DELETE FROM Attended_employee WHERE id = ?");
    $stmt_delete->bind_param("s", $id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // 2. Update the scan_count in the employees table back to 0
    $stmt_update = $conn->prepare("UPDATE employees SET scan_count = 0 WHERE id = ?");
    $stmt_update->bind_param("s", $id);
    $stmt_update->execute();
    $stmt_update->close();

    // If both queries were successful, commit the transaction
    $conn->commit();

    echo json_encode(["success" => true, "message" => "تمت إزالة الحضور بنجاح"]);

} catch (mysqli_sql_exception $exception) {
    // If any query fails, roll back the entire transaction
    $conn->rollback();
    
    // For debugging, you can log the error instead of showing it to the user
    // error_log("Remove attendance failed: " . $exception->getMessage());
    
    http_response_code(500 ); // Internal Server Error
    echo json_encode(["success" => false, "message" => "حدث خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى."]);
}

$conn->close();
?>