<?php
// Database connection
$servername = "193.203.168.53";
$username = "u968010081_mogamaa";
$password = "Mogamaa_2000";
$dbname = "u968010081_mogamaa";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if ID is provided
if (isset($_GET['id'])) {
    $id = $conn->real_escape_string($_GET['id']);

    // Retrieve user data
    $sql = "SELECT * FROM employees WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Get user details
        $serialNumber = $row['id'];
        $name = $row['name']; // ✅ FIXED: Get name from database
        $phone = $row['phone']; // ✅ Get phone from database

        // QR Code URL
        $qrCodeImageUrl = "http://mogamaaa.shamandorascout.com/qrcodes/" . $serialNumber . ".png";

        // WhatsApp message in English + Arabic
        $whatsappMessage = "Hello $name,\n\n"
            . "Thank you for registering with Shamandora Scout. Your Serial Number is: $serialNumber.\n"
            . "You can access your ticket here: $qrCodeImageUrl.\n\n"
            . "مرحباً $name،\n\n"
            . "شكراً لتسجيلك في Shamandora Scout. رقم التسلسل الخاص بك هو: $serialNumber.\n"
            . "يمكنك الوصول إلى تذكرتك هنا: $qrCodeImageUrl.\n"
            . "برجاء تسجيل رقم الهاتف المرسل منه الرساله حتي يمكنكم فتح اللينك.";

        // WhatsApp API URL
        $whatsappUrl = "https://api.whatsapp.com/send?phone=" . urlencode($phone) . "&text=" . urlencode($whatsappMessage);

        // Redirect to WhatsApp
        header("Location: $whatsappUrl");
        exit;
    } else {
        echo "User not found.";
    }
} else {
    echo "No ID provided.";
}

$conn->close();
?>