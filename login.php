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
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .box {
        width: 350px;
        padding: 25px 30px;
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, .1);
        text-align: center;
    }

    h3 {
        margin-bottom: 20px;
        color: #0f766e;
        font-size: 22px;
    }

    input,
    button {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        border-radius: 6px;
        font-size: 15px;
        box-sizing: border-box;
    }

    input {
        border: 1px solid #ccc;
    }

    button {
        background: #0f766e;
        color: #fff;
        border: none;
        cursor: pointer;
        font-weight: bold;
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
        margin-bottom: 12px;
        font-size: 14px;
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