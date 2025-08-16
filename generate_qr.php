<?php
// generate_qrcode.php

// Retrieve parameters
$id = $_GET['id'];
$name = $_GET['name'];
$phone = $_GET['phone'];
$team = $_GET['team'];
$grade = $_GET['grade'];
$payment = $_GET['payment'];

// Prepare the data string for the QR code with only name and payment
$data = "ID: $id\nName: $name\nPayment Amount: $payment\nTeam: $team ";

// Encode the data for the QR code URL
$encodedData = urlencode($data);

// Generate the QR code URL
$qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=" . $encodedData;

// Get the QR code image data
$qrCodeImageData = file_get_contents($qrCodeUrl);

// Define the directory to save the QR code image
$uploadDir = 'qrcodes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Create the directory if it doesn't exist
}

// Sanitize and create the file name using the serial number
$qrCodeFileName = $uploadDir . $id . '.png';

// Save the QR code image to the server
if (file_put_contents($qrCodeFileName, $qrCodeImageData)) {
    // Create the URL to access the QR code image
    $qrCodeImageUrl = 'http://mogamaaa.shamandorascout.com/' . $qrCodeFileName;

    // Store the image URL in the session for later use
    session_start();
    $_SESSION['qrCodeImageUrl'] = $qrCodeImageUrl;
    $_SESSION['name'] = $name;
    $_SESSION['phone'] = $phone;
    $_SESSION['serialNumber'] = $id;

    // Redirect to the page to send the WhatsApp message
    header("Location: send_whatsapp.php");
    exit();
} else {
    echo "Failed to save QR code image.";
}
?>
