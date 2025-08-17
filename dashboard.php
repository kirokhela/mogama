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
@import url("https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap");

body {
    font-family: "Cairo", sans-serif;
    background: #f8fafc;
    margin: 0;
    direction: rtl;
}

/* Container */
.dashboard-container {
    padding: 40px 20px;
    max-width: 1200px;
    margin: auto;
}

/* Title */
.dashboard-title {
    text-align: center;
    font-size: 2.4rem;
    font-weight: 700;
    margin-bottom: 40px;
    color: #1e293b;
}

/* Cards Layout */
.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    justify-content: center;
}

/* Card Style */
.card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.08);
    flex: 1;
    min-width: 270px;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    overflow: hidden;
}

.card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: 0 10px 24px rgba(0,0,0,0.12);
}

.card-header {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff;
    padding: 15px;
    font-size: 1.2rem;
    font-weight: 600;
    text-align: center;
}

.card-header.green {
    background: linear-gradient(135deg, #10b981, #059669);
}

.card-body {
    padding: 20px;
    text-align: center;
}

.card-body p {
    font-size: 1.8rem;
    font-weight: bold;
    color: #0f172a;
    margin: 10px 0;
}

/* Section Titles */
.section-title {
    text-align: center;
    font-size: 1.8rem;
    font-weight: 700;
    margin: 50px 0 25px;
    color: #2563eb;
}

/* Buttons */
.export-btn {
    background: #2563eb;
    color: #fff;
    padding: 9px 18px;
    border-radius: 50px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin: 6px 4px;
}

.export-btn:hover {
    background: #1d4ed8;
    transform: scale(1.07);
}

/* Table */
.payment-table {
    width: 100%;
    max-width: 650px;
    margin: 0 auto 50px;
    border-collapse: collapse;
    border-radius: 14px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 6px 16px rgba(0,0,0,0.08);
}

.payment-table th, .payment-table td {
    padding: 14px;
    text-align: center;
    font-size: 15px;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
}

.payment-table th {
    background: #f1f5f9;
    font-weight: 700;
}

.payment-table tr:nth-child(even) {
    background: #f9fafb;
}

.payment-table tr:hover {
    background: #eff6ff;
}

/* Responsive */
@media(max-width:768px) {
    .cards {
        flex-direction: column;
        align-items: stretch;
    }
    .dashboard-title {
        font-size: 2rem;
    }
}
</style>

<div class="dashboard-container">
    <h1 class="dashboard-title">لوحة التحكم - الكشافة</h1>
    
    <div class="cards">
        <div class="card">
            <div class="card-header"><span>👥 إجمالي الكشافة</span></div>
            <div class="card-body">
                <p>' . $total_scouts_all . '</p>
            </div>
        </div>
        <div class="card">
            <div class="card-header green"><span>💰 إجمالي المدفوعات</span></div>
            <div class="card-body">
                <p>' . number_format($total_payment_all, 2) . ' جنيه</p>
            </div>
        </div>
    </div>

    <h2 class="section-title">🏅 توزيع الفرق</h2>
    <div class="cards">';

foreach ($teams as $team) {
    $count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE team='$team'")->fetch_assoc()['c'];
    $pageContent .= '
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #' . substr(md5($team), 0, 6) . ', #' . substr(md5(strrev($team)), 0, 6) . ');">
                ' . htmlspecialchars($team) . '
            </div>
            <div class="card-body">
                <p>👥 ' . $count . ' عضو</p>
                <a href="team_members.php?team=' . urlencode($team) . '" class="export-btn">👀 عرض الأعضاء</a>
                <a href="dashboard.php?team_export=' . urlencode($team) . '" class="export-btn">⬇️ تحميل CSV</a>
            </div>
        </div>';
}

$pageContent .= '
    </div>

    <h2 class="section-title">💵 توزيع المدفوعات</h2>
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