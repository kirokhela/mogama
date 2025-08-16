<?php
require_once 'includes/auth.php';
require_admin();
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: detail.php');
    exit;
}

// Delete database row
$stmt = $conn->prepare("DELETE FROM employees WHERE id=?");
$stmt->bind_param("s", $id);
$stmt->execute();
$stmt->close();

// Optional: delete QR image file if you store it as qrcodes/{id}.png
$qrFile = __DIR__ . '/qrcodes/' . $id . '.png';
if (file_exists($qrFile)) {
    @unlink($qrFile);
}

header('Location: detail.php');
exit;