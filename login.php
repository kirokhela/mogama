<?php
// login.php
require_once 'includes/auth.php';

// If already logged in redirect
if (is_admin()) {
    header('Location: detail.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');

    if (check_admin_credentials($user, $pass)) {
        do_admin_login($user);
        header('Location: detail.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Login</title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f6f9;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}
.box {
    width: 360px;
    padding: 30px 25px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
    text-align: center;
}
h3 {
    margin-bottom: 20px;
    color: #0f766e;
}
input {
    width: 100%;
    padding: 12px 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
button {
    width: 100%;
    padding: 12px 0;
    margin-top: 10px;
    background: #0f766e;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    transition: background 0.2s;
}
button:hover {
    background: #115e59;
}
.error {
    color: #b91c1c;
    background: #fee2e2;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 10px;
}
.info {
    font-size: 13px;
    color: #555;
    margin-top: 12px;
}
.info strong {
    color: #0f766e;
}
</style>
</head>
<body>
<div class="box">
    <h3>Admin Login</h3>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
        <input name="username" placeholder="Username" required autofocus>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>