<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "193.203.168.53";
$username = "u968010081_mogamaa";
$password = "Mogamaa_2000";
$dbname = "u968010081_mogamaa";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("<div class='notification error'>Connection failed: " . $conn->connect_error . "</div>");
}

// Capture form data with default values
$name    = isset($_POST['name']) ? $_POST['name'] : '';
$phone   = isset($_POST['phone']) ? $_POST['phone'] : '';
$team    = isset($_POST['team']) ? $_POST['team'] : '';
$grade   = isset($_POST['grade']) ? $_POST['grade'] : '';
$payment = isset($_POST['payment']) ? $_POST['payment'] : '';
$isCase  = isset($_POST['isCase']) && $_POST['isCase'] == '1' ? 1 : 0;

// Clean and ensure phone starts with +20
$phone = preg_replace('/\s+/', '', $phone); // Remove spaces
if (!str_starts_with($phone, '+20')) {
    $phone = '+20' . ltrim($phone, '0');
}

// If IsCase is marked, force payment to 0
if ($isCase === 1) {
    $payment = 0;
}

// Initialize validation
$valid = true;
$error_messages = [];

// Validate phone number format (must be +20XXXXXXXXXX)
if (!preg_match('/^\+20\d{10}$/', $phone)) {
    $valid = false;
    $error_messages[] = "رقم الهاتف يجب أن يبدأ بـ +20 ويكون 13 رقم.";
}

// Validate payment only if not IsCase
if ($isCase === 0 && !preg_match('/^\d+$/', $payment)) {
    $valid = false;
    $error_messages[] = "المبلغ يجب أن يكون أرقام فقط.";
}

// Display errors or insert into database
if (!$valid) {
    echo "<div class='notification error'><ul>";
    foreach ($error_messages as $message) {
        echo "<li>" . htmlspecialchars($message) . "</li>";
    }
    echo "</ul></div>";
} else {
    // Generate a 4-character unique ID
    $id = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 4);

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO employees (id, name, phone, team, grade, payment, IsCase) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssi", $id, $name, $phone, $team, $grade, $payment, $isCase);

    if ($stmt->execute()) {
        echo "<div class='notification success'>تم إرسال النموذج بنجاح! سيتم تحويلك...</div>";
        header("Refresh: 2; URL=generate_qr.php?id=" . urlencode($id) . "&name=" . urlencode($name) . "&phone=" . urlencode($phone) . "&team=" . urlencode($team) . "&grade=" . urlencode($grade) . "&payment=" . urlencode($payment) . "&isCase=" . urlencode($isCase));
        exit;
    } else {
        echo "<div class='notification error'>خطأ: " . htmlspecialchars($stmt->error) . "</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!-- CSS for messages -->
<style>
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f7f6;
    padding: 20px;
    margin: 0;
}

.notification {
    padding: 20px;
    margin: 20px 0;
    border-radius: 5px;
    font-size: 16px;
}

.notification.error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.notification.success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.notification ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notification ul li {
    margin-bottom: 5px;
}
</style>
