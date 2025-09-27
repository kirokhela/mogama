<?php
// Enable error reporting for debugging (turn off in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ---- DB CONNECTION ----
$servername = "193.203.168.53";
$username   = "u968010081_mogamaa";
$password   = "Mogamaa_2000";
$dbname     = "u968010081_mogamaa";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("<div class='notification error'>Connection failed: " . $conn->connect_error . "</div>");
}
$conn->set_charset("utf8mb4");

// ---- CAPTURE FORM DATA ----
$name    = isset($_POST['name'])    ? trim($_POST['name'])    : '';
$phone   = isset($_POST['phone'])   ? trim($_POST['phone'])   : '';
$team    = isset($_POST['team'])    ? trim($_POST['team'])    : '';
$grade   = isset($_POST['grade'])   ? trim($_POST['grade'])   : '';
$payment = isset($_POST['payment']) ? trim($_POST['payment']) : '';
$isCase  = (isset($_POST['isCase']) && $_POST['isCase'] == '1') ? 1 : 0;

// ---- NORMALIZE PHONE: force +20 --------
$phone = preg_replace('/\s+/', '', $phone); // remove spaces
if (strpos($phone, '+20') !== 0) {
    $phone = '+20' . ltrim($phone, '0');
}

// ---- VALIDATION ----
$valid = true;
$error_messages = [];

if ($name === '') {
    $valid = false;
    $error_messages[] = "الاسم مطلوب.";
}

if (!preg_match('/^\+20\d{10}$/', $phone)) {
    $valid = false;
    $error_messages[] = "رقم الهاتف يجب أن يبدأ بـ +20 ويكون 13 رقم.";
}

if ($team === '') {
    $valid = false;
    $error_messages[] = "الفريق مطلوب.";
}

/**
 * NEW: Always require payment to be numeric
 * - allows integers or decimals, e.g., 0, 50, 50.00
 * - does NOT force 0 when isCase=1 (cases might pay 50)
 */
if ($payment === '' || !preg_match('/^\d+(\.\d+)?$/', $payment)) {
    $valid = false;
    $error_messages[] = "المبلغ يجب أن يكون رقمًا صالحًا (مثال: 0 أو 50 أو 50.00).";
}

// ---- SHOW ERRORS (IF ANY) ----
if (!$valid) {
    echo "<div class='notification error'><ul>";
    foreach ($error_messages as $message) {
        echo "<li>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</li>";
    }
    echo "</ul></div>";
    $conn->close();
    exit;
}

// ---- GENERATE UNIQUE 4-CHAR ID ----
function generateId() {
    $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $shuffled = str_shuffle($chars);
    return substr($shuffled, 0, 4);
}

$id = '';
$maxAttempts = 10;
for ($i = 0; $i < $maxAttempts; $i++) {
    $candidate = generateId();
    $stmtCheck = $conn->prepare("SELECT id FROM employees WHERE id = ?");
    $stmtCheck->bind_param("s", $candidate);
    $stmtCheck->execute();
    $res = $stmtCheck->get_result();
    $stmtCheck->close();
    if ($res->num_rows === 0) {
        $id = $candidate;
        break;
    }
}
if ($id === '') {
    echo "<div class='notification error'>تعذر إنشاء كود فريد. حاول مرة أخرى.</div>";
    $conn->close();
    exit;
}

// ---- TRANSACTION: insert employee + mark attendance + update scan_count ----
$conn->begin_transaction();
try {
    // 1) Insert into employees (keep payment as provided)
    $stmtEmp = $conn->prepare("
        INSERT INTO employees (id, name, phone, team, grade, payment, IsCase, scan_count)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0)
    ");
    if (!$stmtEmp) {
        throw new Exception("Prepare failed (employees): " . $conn->error);
    }
    $stmtEmp->bind_param("sssssis", $id, $name, $phone, $team, $grade, $payment, $isCase);
    if (!$stmtEmp->execute()) {
        throw new Exception("Insert failed (employees): " . $stmtEmp->error);
    }
    $stmtEmp->close();

    // 2) Insert attendance (ALWAYS with the given payment value, not forced to 0)
    $stmtChkAtt = $conn->prepare("SELECT id FROM Attended_employee WHERE id = ?");
    if (!$stmtChkAtt) {
        throw new Exception("Prepare failed (check attend): " . $conn->error);
    }
    $stmtChkAtt->bind_param("s", $id);
    $stmtChkAtt->execute();
    $attRes = $stmtChkAtt->get_result();
    $stmtChkAtt->close();

    if ($attRes->num_rows === 0) {
        $payment_amount = (float)$payment;
        $stmtAtt = $conn->prepare("
            INSERT INTO Attended_employee (id, name, payment_amount, team)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmtAtt) {
            throw new Exception("Prepare failed (attend): " . $conn->error);
        }
        $stmtAtt->bind_param("ssds", $id, $name, $payment_amount, $team);
        if (!$stmtAtt->execute()) {
            throw new Exception("Insert failed (attend): " . $stmtAtt->error);
        }
        $stmtAtt->close();
    }

    // 3) Update scan_count = 1
    $stmtUpd = $conn->prepare("UPDATE employees SET scan_count = 1 WHERE id = ?");
    if (!$stmtUpd) {
        throw new Exception("Prepare failed (scan_count): " . $conn->error);
    }
    $stmtUpd->bind_param("s", $id);
    if (!$stmtUpd->execute()) {
        throw new Exception("Update failed (scan_count): " . $stmtUpd->error);
    }
    $stmtUpd->close();

    // Commit all
    $conn->commit();

    // ---- SUCCESS UI + ANIMATION + AUTO REFRESH ----
    ?>
<div class="success-overlay show" dir="rtl">
    <div class="success-card">
        <svg class="checkmark" viewBox="0 0 52 52" aria-hidden="true">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark__check" fill="none" d="M14 27 l8 8 16-16" />
        </svg>

        <h2>تم إدخال الشخص وتسجيله بنجاح</h2>
        <p>
            الكود: <strong><?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></strong> —
            الاسم: <strong><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></strong> —
            الفريق: <strong><?= htmlspecialchars($team, ENT_QUOTES, 'UTF-8') ?></strong> —
            المبلغ: <strong><?= htmlspecialchars($payment, ENT_QUOTES, 'UTF-8') ?></strong>
        </p>

        <div class="progress"></div>
        <small>سيتم إعادة تحميل الصفحة الآن…</small>
    </div>
</div>

<script>
setTimeout(function() {
    window.location.href = "fawry.php";
}, 1500);
</script>
<?php

} catch (Exception $e) {
    $conn->rollback();
    echo "<div class='notification error'>حدث خطأ أثناء الحفظ: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</div>";
}

$conn->close();
?>

<!-- Minimal base styles -->
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
    border-radius: 8px;
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

/* Success overlay animation */
.success-overlay {
    position: fixed;
    inset: 0;
    background: rgba(255, 255, 255, 0.92);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity .25s ease;
    z-index: 9999;
}

.success-overlay.show {
    opacity: 1;
    pointer-events: auto;
}

.success-card {
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, .1);
    padding: 28px 24px;
    text-align: center;
    max-width: 520px;
    width: 92%;
    animation: pop-in .25s ease-out both;
}

@keyframes pop-in {
    from {
        transform: translateY(10px) scale(.98);
        opacity: .0;
    }

    to {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
}

.checkmark {
    width: 84px;
    height: 84px;
    display: block;
    margin: 0 auto 14px auto;
}

.checkmark__circle {
    stroke: #28a745;
    stroke-width: 3;
    stroke-miterlimit: 10;
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    animation: dash 0.6s ease-in-out forwards;
}

.checkmark__check {
    stroke: #28a745;
    stroke-width: 3;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: dash-check 0.35s 0.6s ease-in-out forwards;
}

@keyframes dash {
    to {
        stroke-dashoffset: 0;
    }
}

@keyframes dash-check {
    to {
        stroke-dashoffset: 0;
    }
}

.progress {
    height: 4px;
    background: #e9ecef;
    border-radius: 999px;
    overflow: hidden;
    margin: 14px 0 6px;
    position: relative;
}

.progress::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 0%;
    background: #28a745;
    animation: fill 1.3s linear forwards;
}

@keyframes fill {
    to {
        width: 100%;
    }
}

h2 {
    margin: 8px 0 6px;
    font-size: 20px;
    color: #1b1e21;
}

p {
    margin: 0 0 8px;
    color: #495057;
    font-size: 15px;
}

small {
    color: #6c757d;
}
</style>