<?php
session_start();
require_once 'db.php';

// Set active page for sidebar
$activePage = 'dashboard.php';

// --- الحصول على الفرق ---
$teams_result = $conn->query("SELECT DISTINCT team FROM employees");
$teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $teams[] = $row['team'];
}

// --- الملخص ---
$total_scouts_all = $conn->query("SELECT COUNT(*) as c FROM employees")->fetch_assoc()['c'];
$total_payment_all = $conn->query("SELECT SUM(payment) as sum_pay FROM employees")->fetch_assoc()['sum_pay'];

// --- توزيع المدفوعات ---
$payment_dist = [];
$payment_query = $conn->query("SELECT ROUND(payment,2) as pay, COUNT(*) as count FROM employees GROUP BY pay ORDER BY pay ASC");
while ($row = $payment_query->fetch_assoc()) {
    $payment_dist[$row['pay']] = $row['count'];
}

// --- Cards per day (total scouts + total payment) ---
$daily_stats = [];
$daily_query = $conn->query("
    SELECT DATE(created_at) as day,
           COUNT(*) as members_count,
           SUM(payment) as total_payment
    FROM employees
    GROUP BY DATE(created_at)
    ORDER BY day DESC
    LIMIT 7
");
while ($row = $daily_query->fetch_assoc()) {
    $daily_stats[] = $row;
}

// --- تصدير CSV لكل فريق ---
if (isset($_GET['team_export'])) {
    $team_name = $conn->real_escape_string($_GET['team_export']);
    header('Content-Type:text/csv');
    header('Content-Disposition:attachment;filename="'.$team_name.'_members.csv"');
    $output = fopen('php://output', 'w');
    $res = $conn->query("SHOW COLUMNS FROM employees");
    $cols = [];
    while($c = $res->fetch_assoc()) $cols[] = $c['Field'];
    fputcsv($output, $cols);
    $members = $conn->query("SELECT * FROM employees WHERE team='$team_name'");
    while($row = $members->fetch_assoc()) fputcsv($output, $row);
    fclose($output);
    exit();
}

// Dashboard content
$pageContent = '
<style>
/* your same CSS above ... */

/* Extra for daily cards */
.daily-card {
    border-top: 5px solid #f59e0b;
}
</style>

<div class="dashboard-container">
    <h1 class="dashboard-title">لوحة التحكم - الكشافة</h1>
    
    <div class="cards">
        <div class="card total-card">
            <h3>إجمالي الكشافة</h3>
            <p>' . $total_scouts_all . '</p>
        </div>
        <div class="card total-card">
            <h3>إجمالي المدفوعات</h3>
            <p>' . number_format($total_payment_all, 2) . ' جنيه</p>
        </div>
    </div>

    <h2 class="section-title">إحصائيات الأيام الأخيرة</h2>
    <div class="cards">';
foreach ($daily_stats as $stat) {
    $pageContent .= '
        <div class="card daily-card">
            <h3>' . htmlspecialchars($stat['day']) . '</h3>
            <p>عدد الأعضاء: ' . $stat['members_count'] . '</p>
            <p>إجمالي المدفوعات: ' . number_format($stat['total_payment'], 2) . ' جنيه</p>
        </div>';
}
$pageContent .= '
    </div>

    <h2 class="section-title">توزيع الفرق</h2>
    <div class="cards">';

foreach ($teams as $team) {
    $count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE team='$team'")->fetch_assoc()['c'];
    $pageContent .= '
        <div class="card" style="border-top:5px solid #' . substr(md5($team), 0, 6) . '">
            <h3>' . htmlspecialchars($team) . '</h3>
            <p>عدد: ' . $count . '</p>
            <a href="team_members.php?team=' . urlencode($team) . '" class="export-btn">عرض الأعضاء</a>
            <a href="dashboard.php?team_export=' . urlencode($team) . '" class="export-btn">تحميل CSV</a>
        </div>';
}

$pageContent .= '
    </div>

    <h2 class="section-title">توزيع المدفوعات</h2>
    <table class="payment-table">
        <thead>
            <tr>
                <th>المبلغ المدفوع</th>
                <th>عدد الأعضاء</th>
            </tr>
        </thead>
        <tbody>';

foreach ($payment_dist as $amount => $count) {
    $pageContent .= '
            <tr>
                <td>' . number_format($amount, 2) . ' جنيه</td>
                <td>' . $count . '</td>
            </tr>';
}

$pageContent .= '
        </tbody>
    </table>
</div>';

// Include the layout
include "layout.php";
?>