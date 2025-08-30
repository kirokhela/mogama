<?php
require_once 'db.php'; // Your database connection file
error_reporting(E_ALL);
ini_set('display_errors', 1);
// New date you want to set
$new_date = '2025-08-28';

// Member's ID
$id = '1N6Z';

// Update query (keep time, change only date)
$sql = "UPDATE employees 
        SET timestamp = CONCAT(?, ' ', TIME(`timestamp`)) 
        WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $new_date, $id);

if ($stmt->execute()) {
    echo "Date updated successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>


