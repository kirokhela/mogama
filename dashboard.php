<?php
session_start();
require_once 'db.php';

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
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة التحكم - الكشافة</title>
    <style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
        background: #f4f4f4;
        color: #333;
        display: flex;
        direction: rtl;
    }

    .main-content {
        margin-right: 220px;
        padding: 30px;
        width: 100%;
    }

    @media(max-width:768px) {
        .main-content {
            margin-right: 60px;
        }

        .cards {
            flex-direction: column;
        }
    }

    .cards {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 40px;
    }

    .card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        flex: 1;
        min-width: 220px;
        text-align: center;
    }

    .card h3 {
        margin-bottom: 15px;
        color: #0f766e;
    }

    .card p {
        font-size: 18px;
        font-weight: bold;
        margin: 8px 0;
    }

    .total-card {
        background: #1abc9c;
        color: #fff;
        border-top: 5px solid #16a085;
    }

    .total-card h3,
    .total-card p {
        color: #fff;
    }

    .export-btn {
        background: #0f766e;
        color: #fff;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        text-decoration: none;
        margin: 5px;
        display: inline-block;
    }

    .export-btn:hover {
        background: #0d665b;
    }

    .payment-table {
        width: 100%;
        max-width: 500px;
        margin: 0 auto 40px auto;
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .payment-table th,
    .payment-table td {
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    .payment-table th {
        background: #0f766e;
        color: #fff;
    }

    .payment-table tr:last-child td {
        border-bottom: none;
    }

    /* Responsive */
    @media(max-width:1024px) {
        .main-content {
            margin-right: 180px;
        }

        .sidenav {
            width: 180px;
        }
    }

    @media(max-width:768px) {
        body {
            flex-direction: column;
        }

        .sidenav {
            position: relative;
            width: 100%;
            height: auto;
        }

        .main-content {
            margin-right: 0;
            width: 100%;
            padding: 20px;
        }

        .cards {
            justify-content: center;
        }

        .card {
            max-width: 90%;
        }
    }

    @media(max-width:480px) {
        .card {
            max-width: 100%;
        }
    }
    </style>
</head>

<body>

    <?php include 'sidenav.php'; ?>

    <div class="main-content">
        <h1>لوحة التحكم - الكشافة</h1>
        <div class="cards">
            <div class="card total-card">
                <h3>إجمالي الكشافة</h3>
                <p><?= $total_scouts_all ?></p>
            </div>
            <div class="card total-card">
                <h3>إجمالي المدفوعات</h3>
                <p><?= number_format($total_payment_all,2) ?> جنيه</p>
            </div>
        </div>

        <h2>توزيع الفرق</h2>
        <h2>توزيع الفرق</h2>
        <div class="cards">
            <?php foreach ($teams as $team): ?>
            <?php $count = $conn->query("SELECT COUNT(*) as c FROM employees WHERE team='$team'")->fetch_assoc()['c']; ?>
            <div class="card" style="border-top:5px solid #<?= substr(md5($team),0,6) ?>">
                <h3><?= htmlspecialchars($team) ?></h3>
                <p>عدد الكشافة: <?= $count ?></p>
                <a href="team_members.php?team=<?= urlencode($team) ?>" class="export-btn">عرض الأعضاء</a>
                <a href="dashboard.php?team_export=<?= urlencode($team) ?>" class="export-btn">تحميل CSV</a>
            </div>
            <?php endforeach; ?>
        </div>

        <h2>توزيع المدفوعات</h2>
        <h2>توزيع المدفوعات</h2>
        <table class="payment-table">
            <thead>
                <tr>
                    <th>المبلغ المدفوع</th>
                    <th>عدد الأعضاء</th>
                </tr>
                <tr>
                    <th>المبلغ المدفوع</th>
                    <th>عدد الأعضاء</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payment_dist as $amount => $count): ?>
                <tr>
                    <td><?= number_format($amount,2) ?> جنيه</td>
                    <td><?= $count ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>