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
.dashboard-container {
    font-family: "Cairo", sans-serif;
    background: white;
    padding: 30px;
    direction: rtl;
    text-align: center;
}

.dashboard-title {
    text-align: center;
    color: #2d3748;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 30px;
}

.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 40px;
    justify-content: center;
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    flex: 1;
    min-width: 220px;
    text-align: center;
}

.card h3 {
    margin-bottom: 15px;
    color: #2c3e50;
    font-weight: 600;
}

.card p {
    font-size: 18px;
    font-weight: bold;
    margin: 8px 0;
    text-align: center;
}

.total-card {
    background: #2c3e50;
    color: #fff;
    border-top: 5px solid #0d665b;
}

.total-card h3,
.total-card p {
    color: #fff;
}

.section-title {
    color: #3498db;
    font-size: 1.8rem;
    font-weight: 600;
    margin: 30px 0 20px 0;
    text-align: center;
}

.export-btn {
    background: #2c3e50;
    color: #fff;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    margin: 5px;
    display: inline-block;
    font-size: 13px;
    transition: background 0.2s;
}

.export-btn:hover {
    background: #3498db;
}

.payment-table {
    width: 100%;
    max-width: 500px;
    margin: 0 auto 40px auto;
    border-collapse: collapse;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.payment-table th,
.payment-table td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
}

.payment-table th {
    background: #f8fafc;
    color: #000;
    font-weight: 600;
}

.payment-table tr:nth-child(even) {
    background: #fafafa;
}

.payment-table tr:hover {
    background: #f1f5f9;
}

/* Responsive */
@media(max-width:768px) {
    .dashboard-container {
        padding: 20px;
    }
    
    .dashboard-title {
        font-size: 2rem;
    }
    
    .cards {
        flex-direction: column;
        align-items: center;
    }
    
    .card {
        max-width: 90%;
    }
    
    .export-btn {
        display: block;
        margin: 5px 0;
    }
}

@media(max-width:480px) {
    .card {
        max-width: 100%;
    }
    
    .payment-table {
        max-width: 100%;
    }
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

    <h2 class="section-title">توزيع الفرق</h2>
    <div class="cards">';

foreach ($teams as $team) {
    $count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE team='$team'")->fetch_assoc()['c'];
    $pageContent .= '
        <div class="card" style="border-top:5px solid #' . substr(md5($team), 0, 6) . '">
            <h3>' . htmlspecialchars($team) . '</h3>
            <p>عدد الكشافة: ' . $count . '</p>
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
include 'layout.php';
?>