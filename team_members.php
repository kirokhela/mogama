<?php
session_start();
require_once 'db.php';

$team = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
if (!$team) die("No team specified.");

// --- CSV download ---
if (isset($_GET['download_csv'])) {
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment;filename="'.$team.'_members.csv"');
    echo "\xEF\xBB\xBF"; // BOM

    $output = fopen('php://output', 'w');

    // Keep only these columns
    $cols = ['id', 'name', 'Timestamp', 'payment', 'IsCase'];

    // Header row
    fputcsv($output, ['#', 'ID', 'Name', 'Date', 'Payment', 'IsCase']);

    $members = $conn->query("SELECT * FROM employees WHERE team='$team'");
    $counter = 1;
    while($row = $members->fetch_assoc()) {
        $data = [];

        foreach ($cols as $col) {
            if ($col === 'IsCase') {
                $data[] = $row[$col] == 1 ? "Yes" : "No";
            } elseif ($col === 'Timestamp') {
                // Show only date part
                $data[] = date("Y-m-d", strtotime($row[$col]));
            } else {
                $data[] = $row[$col];
            }
        }

        fputcsv($output, array_merge([$counter++], $data));
    }
    fclose($output);
    exit();
}

// --- Fetch for display ---
$members = $conn->query("SELECT * FROM employees WHERE team='$team'");
$cols = ['id', 'name', 'Timestamp', 'payment', 'IsCase'];
?>
<!DOCTYPE html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>أعضاء الفريق - <?= htmlspecialchars($team) ?></title>
    <style>
    body {
        font-family: "Segoe UI", Arial, sans-serif;
        margin: 0;
        background: #f4f4f4;
        color: #333;
        display: flex;
    }

    .main-content {
        margin-right: 220px;
        padding: 30px;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h1 {
        text-align: center;
        color: #0f766e;
    }

    .cards {
        display: flex;
        gap: 20px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #0f766e;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        text-align: center;
    }

    .btn:hover {
        background: #0d665b;
    }

    table {
        width: 100%;
        max-width: 800px;
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
    }

    table th,
    table td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #eee;
    }

    table th {
        background: #0f766e;
        color: #fff;
    }

    tr:hover {
        background: #f1f1f1;
    }
    </style>
</head>

<body>
    <?php include 'sidenav.php'; ?>
    <div class="main-content">
        <h1>أعضاء فريق <?= htmlspecialchars($team) ?></h1>
        <div class="cards">
            <a href="dashboard.php" class="btn">⬅ رجوع للرئيسية</a>
            <a href="team_members.php?team=<?= urlencode($team) ?>&download_csv=1" class="btn">📥 تحميل CSV</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>الاسم</th>
                    <th>التاريخ</th>
                    <th>الدفع</th>
                    <th>IsCase</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php while($row = $members->fetch_assoc()): ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= date("Y-m-d", strtotime($row['Timestamp'])) ?></td>
                    <td><?= htmlspecialchars($row['payment']) ?></td>
                    <td><?= $row['IsCase'] == 1 ? "Yes" : "No" ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>