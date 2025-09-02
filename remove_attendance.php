<?php
header('Content-Type: application/json; charset=utf-8');
// In production, replace '*' with your actual domain
header('Access-Control-Allow-Origin: *'); 
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Handle preflight (CORS) requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// --- Database Connection ---
$servername = "193.203.168.53";
$username   = "u968010081_mogamaa";
$password   = "Mogamaa_2000";
$dbname     = "u968010081_mogamaa";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection failed."]);
    exit;
}
$conn->set_charset("utf8mb4");

// --- Validate Request Method ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// --- Read Input (Support JSON + Form Data) ---
$input = json_decode(file_get_contents("php://input"), true);
$id = $input['id'] ?? ($_POST['id'] ?? null);

if (!$id) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Employee ID is required."]);
    exit;
}

// --- Start Transaction ---
$conn->begin_transaction();

try {
    // 1. Delete from Attended_employee
    $stmt_delete = $conn->prepare("DELETE FROM Attended_employee WHERE id = ?");
    if (!$stmt_delete) {
        throw new mysqli_sql_exception("Prepare failed: " . $conn->error);
    }
    $stmt_delete->bind_param("s", $id);
    $stmt_delete->execute();
    $stmt_delete->close();

    // 2. Reset scan_count in employees table
    $stmt_update = $conn->prepare("UPDATE employees SET scan_count = 0 WHERE id = ?");
    if (!$stmt_update) {
        throw new mysqli_sql_exception("Prepare failed: " . $conn->error);
    }
    $stmt_update->bind_param("s", $id);
    $stmt_update->execute();
    $stmt_update->close();

    // Commit changes
    $conn->commit();

    echo json_encode(["success" => true, "message" => "تمت إزالة الحضور بنجاح"]);

} catch (mysqli_sql_exception $exception) {
    // Rollback on error
    $conn->rollback();

    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "حدث خطأ في قاعدة البيانات. يرجى المحاولة مرة أخرى.",
        "error"   => $exception->getMessage() // ⚠️ Optional: remove in production
    ]);
}

$conn->close();?>