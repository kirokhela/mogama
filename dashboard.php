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

// --- إجمالي المدفوعات لفريق أهالي ---
$total_payment_ahaly = $conn->query("SELECT SUM(payment) as total FROM employees WHERE team='أهالي'")
                            ->fetch_assoc()['total'];

// --- إجمالي المدفوعات لغير أهالي ---
$total_payment_non_ahaly = $conn->query("SELECT SUM(payment) as total FROM employees WHERE team!='أهالي'")
                                ->fetch_assoc()['total'];

// --- إحصائيات اليوم ---
$today_stats = $conn->query("
    SELECT COUNT(*) as members_count, SUM(payment) as total_payment
    FROM employees
    WHERE DATE(`Timestamp`) = CURDATE()
")->fetch_assoc();

// --- إحصائيات الأيام ---
$days_stats = [];
$days_query = $conn->query("
    SELECT DATE(`Timestamp`) as day, COUNT(*) as members_count, SUM(payment) as total_payment
    FROM employees
    GROUP BY day
    ORDER BY day DESC"
);
while ($row = $days_query->fetch_assoc()) {
    $days_stats[] = $row;
}

// --- توزيع المدفوعات ---
$payment_dist = [];
$payment_query = $conn->query("SELECT ROUND(payment,2) as pay, COUNT(*) as count FROM employees GROUP BY pay ORDER BY pay ASC");
while ($row = $payment_query->fetch_assoc()) {
    $payment_dist[$row['pay']] = $row['count'];
}

// --- تصدير CSV لكل فريق ---
if (isset($_GET['team_export'])) {
    $team_name = $conn->real_escape_string($_GET['team_export']);
    header('Content-Type:text/csv; charset=UTF-8');
    header('Content-Disposition:attachment;filename="'.$team_name.'_members.csv"');
    $output = fopen('php://output', 'w');

    fprintf($output, "\xEF\xBB\xBF");
    fputcsv($output, ['id','name','phone','team','grade','payment','isCase']);

    $members = $conn->query("SELECT * FROM employees WHERE team='$team_name'");
    while($row = $members->fetch_assoc()) {
        $id = "'" . $row['id'];
        $phone = "\t" . $row['phone'];
        $isCase = isset($row['IsCase']) ? $row['IsCase'] : '';
        fputcsv($output, [$id,$row['name'],$phone,$row['team'],$row['grade'],$row['payment'],$isCase]);
    }
    fclose($output);
    exit();
}

// --- تصدير CSV لكل الأعضاء ---
if (isset($_GET['all_export'])) {
    header('Content-Type:text/csv; charset=UTF-8');
    header('Content-Disposition:attachment;filename="all_members.csv"');
    $output = fopen('php://output', 'w');

    fprintf($output, "\xEF\xBB\xBF");
    fputcsv($output, ['id','name','phone','team','grade','payment','isCase']);

    $members = $conn->query("SELECT * FROM employees");
    while($row = $members->fetch_assoc()) {
        $id = "'" . $row['id'];
        $phone = "\t" . $row['phone'];
        $isCase = isset($row['IsCase']) ? $row['IsCase'] : '';
        fputcsv($output, [$id,$row['name'],$phone,$row['team'],$row['grade'],$row['payment'],$isCase]);
    }
    fclose($output);
    exit();
}

// --- تصدير CSV حسب اليوم ---
if (isset($_GET['day_export'])) {
    $day = $conn->real_escape_string($_GET['day_export']);
    header('Content-Type:text/csv; charset=UTF-8');
    header('Content-Disposition:attachment;filename="members_'.$day.'.csv"');
    $output = fopen('php://output', 'w');

    fprintf($output, "\xEF\xBB\xBF");
    fputcsv($output, ['id','name','phone','team','grade','payment','isCase']);

    $members = $conn->query("SELECT * FROM employees WHERE DATE(`Timestamp`)='$day'");
    while($row = $members->fetch_assoc()) {
        $id = "'" . $row['id'];
        $phone = "\t" . $row['phone'];
        $isCase = isset($row['IsCase']) ? $row['IsCase'] : '';
        fputcsv($output, [$id,$row['name'],$phone,$row['team'],$row['grade'],$row['payment'],$isCase]);
    }
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

.blurred {
    filter: blur(6px);
    transition: filter 0.2s ease;
    cursor: pointer;
}

.blurred:hover {
    filter: blur(0);
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

/* Cards */
.cards {
    display: flex;
    flex-wrap: wrap;
    gap: 25px;
    justify-content: center;
}

.card {
    background: #fff;
    padding: 25px;
    border-radius: 14px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    flex: 1;
    min-width: 260px;
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.12);
}

.card h3 {
    margin-bottom: 12px;
    font-size: 1.3rem;
    font-weight: 600;
    color: #334155;
}

.card p {
    font-size: 1.6rem;
    font-weight: bold;
    margin: 10px 0;
    color: #0f172a;
}

/* Total summary cards */
.total-card {
    border-top: 5px solid #2563eb;
}
.total-card:nth-child(2) {
    border-top: 5px solid #10b981;
}

/* Day cards */
.day-card {
    border-top: 5px solid #f59e0b;
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
    padding: 10px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: 0.2s;
    display: inline-block;
    margin: 6px 4px;
}

.export-btn:hover {
    background: #1d4ed8;
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
    background: #fff;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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

.payment-table tr:hover {
    background: #f9fafb;
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
        <div class="card total-card">
            <h3>إجمالي الكشافة</h3>
            <p>' . $total_scouts_all . '</p>
        </div>
        <div class="card total-card">
            <h3>إجمالي المدفوعات</h3>
            <p><span class="blurred">' . number_format($total_payment_all, 2) . ' جنيه</span></p>
        </div>
        <div class="card total-card">
            <h3>إجمالي أهالي</h3>
            <p><span class="blurred">' . number_format($total_payment_ahaly, 2) . ' جنيه</span></p>
        </div>
        <div class="card total-card">
            <h3>إجمالي غير أهالي</h3>
            <p><span class="blurred">' . number_format($total_payment_non_ahaly, 2) . ' جنيه</span></p>
        </div>
    </div>


    <h2 class="section-title">توزيع الفرق</h2>
    <div class="cards">';

foreach ($teams as $team) {
    $count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE team='$team'")
                  ->fetch_assoc()['c'];
    $sum_pay = $conn->query("SELECT SUM(payment) as total FROM employees WHERE team='$team'")
                    ->fetch_assoc()['total'];

    $pageContent .= '
        <div class="card" style="border-top:5px solid #' . substr(md5($team), 0, 6) . '">
            <h3>' . htmlspecialchars($team) . '</h3>
            <p>عدد: ' . $count . '</p>
            <p><span class="blurred">إجمالي: ' . number_format($sum_pay, 2) . ' جنيه</span></p>
            <a href="team_members.php?team=' . urlencode($team) . '" class="export-btn">عرض الأعضاء</a>
            <a href="dashboard.php?team_export=' . urlencode($team) . '" class="export-btn">تحميل CSV</a>
        </div>';
}

$pageContent .= '
    </div>

    <h2 class="section-title">إحصائيات الأيام السابقة</h2>
    <div class="cards">';
foreach ($days_stats as $day) {
    $pageContent .= '
        <div class="card day-card">
            <h3>' . htmlspecialchars($day['day']) . '</h3>
            <p>الأعضاء: ' . $day['members_count'] . '</p>
            <p><span class="blurred">المدفوعات: ' . number_format($day['total_payment'], 2) . ' جنيه</span></p>
            <a href="dashboard.php?day_export=' . urlencode($day['day']) . '" class="export-btn">تحميل CSV</a>
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
                <td><span class="blurred">' . number_format($amount, 2) . ' جنيه</span></td>
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