<?php
require_once 'db.php'; // your database connection

$result = $conn->query("SHOW COLUMNS FROM employees");

if ($result) {
    echo "<h3>Columns in employees table:</h3><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Field'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Error: " . $conn->error;
}