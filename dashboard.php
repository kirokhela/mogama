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
    margin: 0;
    background: linear-gradient(135deg, #4f46e5, #9333ea);
    font-family: "Cairo", sans-serif;
    color: #fff;
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
    font-size: 2.8rem;
    font-weight: 700;
    margin-bottom: 40px;
    color: #fff;
    letter-spacing: 1px;
}

/* Cards */
.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    justify-content: center;
}

.card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    flex: 1;
    min-width: 250px;
    text-align: center;
    transition: transform 0.2s, background 0.2s;
}

.card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.15);
}

.card h3 {
    margin-bottom: 15px;
    font-size: 1.4rem;
    font-weight: 600;
    color: #fff;
}

.card p {
    font-size: 1.6rem;
    font-weight: bold;
    margin: 10px 0;
    color: #f9fafb;
}

/* Section Titles */
.section-title {
    text-align: center;
    font-size: 2rem;
    font-weight: 700;
    margin: 50px 0 25px;
    color: #facc15;
}

/* Buttons */
.export-btn {
    background: #facc15;
    color: #000;
    padding: 10px 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s;
    display: inline-block;
    margin: 6px 4px;
}

.export-btn:hover {
    background: #fde047;
    transform: scale(1.05);
}

/* Table */
.payment-table {
    width: 100%;
    max-width: 600px;
    margin: 0 auto 50px;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(8px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.15);
}

.payment-table th, .payment-table td {
    padding: 14px;
    text-align: center;
    font-size: 15px;
    color: #fff;
    border-bottom: 1px solid rgba(255,255,255,0.2);
}

.payment-table th {
    background: rgba(0,0,0,0.3);
    font-weight: 700;
}

.payment-table tr:hover {
    background: rgba(255,255,255,0.15);
}

/* Responsive */
@media(max-width:768px) {
    .cards {
        flex-direction: column;
        align-items: stretch;
    }
    .dashboard-title {
        font-size: 2.2rem;
    }
}
</style>

<div class="dashboard-container">
    <h1 class="dashboard-title">✨ لوحة التحكم - الكشافة ✨</h1>
    
    <div class="cards">
        <div class="card">
            <h3>إجمالي الكشافة</h3>
            <p>' . $total_scouts_all . '</p>
        </div>
        <div class="card">
            <h3>إجمالي المدفوعات</h3>
            <p>' . number_format($total_payment_all, 2) . ' جنيه</p>
        </div>
    </div>

    <h2 class="section-title">📌 توزيع الفرق</h2>
    <div class="cards">';

foreach ($teams as $team) {
    $count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE team='$team'")->fetch_assoc()['c'];
    $pageContent .= '
        <div class="card">
            <h3>' . htmlspecialchars($team) . '</h3>
            <p>عدد الكشافة: ' . $count . '</p>
            <a href="team_members.php?team=' . urlencode($team) . '" class="export-btn">👥 عرض الأعضاء</a>
            <a href="dashboard.php?team_export=' . urlencode($team) . '" class="export-btn">⬇️ تحميل CSV</a>
        </div>';
}

$pageContent .= '
    </div>

    <h2 class="section-title">💰 توزيع المدفوعات</h2>
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