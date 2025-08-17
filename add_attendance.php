<?php
header('Content-Type: application/json');
require_once "db.php"; // Make sure this connects to the DB and sets up $conn

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$id     = $_POST['id']             ?? null;
$name   = $_POST['name']           ?? null;
$team   = $_POST['team']           ?? null;
$amount = $_POST['payment_amount'] ?? null;

// Validate all required fields are present
if (!$id || !$name || !$team || !$amount) {
    // It's better to give a more specific error message
    echo json_encode(["success" => false, "message" => "Missing required employee data."]);
    exit;
}

// Check if the employee has already attended
$check = $conn->prepare("SELECT id FROM Attended_employee WHERE id = ?");
$check->bind_param("s", $id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "هذا المستخدم مسجل بالفعل"]);
    exit;
}
$check->close();

// === Start Transaction ===
$conn->begin_transaction();

try {
    // 1. Insert into Attended_employee table
    $stmt_insert = $conn->prepare("INSERT INTO Attended_employee (id, name, payment_amount, team) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssds", $id, $name, $amount, $team);
    $stmt_insert->execute();
    $stmt_insert->close();

    // 2. Update scan_count in the employees table
    $stmt_update = $conn->prepare("UPDATE employees SET scan_count = 1 WHERE id = ?");
    $stmt_update->bind_param("s", $id);
    $stmt_update->execute();
    $stmt_update->close();

    // If both queries were successful, commit the transaction
    $conn->commit();

    echo json_encode(["success" => true, "message" => "تم تسجيل حضور " . htmlspecialchars($name)]);

} catch (mysqli_sql_exception $exception) {
    // If any query fails, roll back the transaction
    $conn->rollback();
    
    // Log the actual error for debugging instead of showing it to the user
    // error_log($exception->getMessage()); 
    echo json_encode(["success" => false, "message" => "Database error occurred. Please try again."]);
}

$conn->close();
?>