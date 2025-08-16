<?php
require_once 'includes/auth.php';
require_admin();            // block non-admins
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: detail.php');
    exit;
}

// On POST -> update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $team    = trim($_POST['team'] ?? '');
    $grade   = trim($_POST['grade'] ?? '');
    $payment = trim($_POST['payment'] ?? '');

    $stmt = $conn->prepare("UPDATE employees SET name=?, phone=?, team=?, grade=?, payment=? WHERE id=?");
    $stmt->bind_param("ssssss", $name, $phone, $team, $grade, $payment, $id);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: detail.php');
        exit;
    } else {
        $error = $stmt->error;
        $stmt->close();
    }
}

// fetch existing row
$stmt = $conn->prepare("SELECT id, name, phone, team, grade, payment FROM employees WHERE id=? LIMIT 1");
$stmt->bind_param("s", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    header('Location: detail.php');
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit - <?php echo htmlspecialchars($row['id']); ?></title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f6f9;
    padding: 20px;
    color: #333;
}
.form-box {
    max-width: 600px;
    margin: 0 auto;
    background: #fff;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,.08);
}
h3 {
    color: #0f766e;
    margin-bottom: 20px;
}
label {
    display: block;
    margin-top: 15px;
    font-weight: 500;
}
input, select {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
button {
    margin-top: 20px;
    padding: 10px 18px;
    background: #0f766e;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
}
button:hover {
    background: #115e59;
}
a.cancel {
    display: inline-block;
    margin-left: 15px;
    margin-top: 20px;
    text-decoration: none;
    color: #555;
    font-weight: 500;
    transition: color 0.2s;
}
a.cancel:hover {
    color: #0f766e;
}
.error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}
</style>
</head>
<body>
<div class="form-box">
  <h3>Edit ID: <?php echo htmlspecialchars($row['id']); ?></h3>
  <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post">
    <label>Name</label>
    <input name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
    
    <label>Phone</label>
    <input name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
    
    <label>Team</label>
    <input name="team" value="<?php echo htmlspecialchars($row['team']); ?>" required>
    
    <label>Grade</label>
    <input name="grade" value="<?php echo htmlspecialchars($row['grade']); ?>" required>
    
    <label>Payment</label>
    <input name="payment" value="<?php echo htmlspecialchars($row['payment']); ?>" required>
    
    <button type="submit">Save</button>
    <a href="detail.php" class="cancel">Cancel</a>
  </form>
</div>
</body>
</html>