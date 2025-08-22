<?php
session_start();
require_once 'db.php';

$team = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
if (!$team) die("No team specified.");

// ================== CSV DOWNLOAD ==================
if (isset($_GET['download_csv'])) {
    $sql = "SELECT id, name, grade, payment, IsCase 
            FROM employees 
            WHERE team = '$team'";
    $members = $conn->query($sql);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment;filename="'.$team.'_members.csv"');
    echo "\xEF\xBB\xBF"; 

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'NAME', 'Grade', 'Payment', 'IsCase']);

    while ($row = $members->fetch_assoc()) {
        $isCase = $row['IsCase'] == 1 ? "Yes" : "No";
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['grade'],
            $row['payment'],
            $isCase
        ]);
    }
    fclose($output);
    exit;
}
// ================== END CSV DOWNLOAD ==================

$members = $conn->query("SELECT * FROM employees WHERE team='$team'");
$res = $conn->query("SHOW COLUMNS FROM employees");
$cols = [];
while ($c = $res->fetch_assoc()) {
    if ($c['Field'] != 'scan_count') {
        $cols[] = $c['Field'];
    }
}
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
        flex-direction: row;
    }

    .main-content {
        margin-right: 220px;
        padding: 20px;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    h1 {
        text-align: center;
        color: #0f766e;
        font-size: 1.5rem;
    }

    .cards {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        justify-content: center;
    }

    .btn {
        display: inline-block;
        padding: 10px 15px;
        background: #0f766e;
        color: #fff;
        text-decoration: none;
        border-radius: 6px;
        text-align: center;
        font-size: 14px;
        min-width: 120px;
    }

    .btn:hover {
        background: #0d665b;
    }

    .table-container {
        width: 100%;
        overflow-x: auto; /* ✅ Scroll on small screens */
    }

    table {
        width: 100%;
        min-width: 600px; /* prevent squishing too much */
        border-collapse: collapse;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        margin-top: 20px;
    }

    table th,
    table td {
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #eee;
        font-size: 14px;
    }

    table th {
        background: #0f766e;
        color: #fff;
    }

    tr:hover {
        background: #f1f1f1;
    }

    /* ✅ Responsive adjustments */
    @media (max-width: 768px) {
        body {
            flex-direction: column;
        }

        .main-content {
            margin-right: 0;
            padding: 15px;
        }

        .btn {
            flex: 1 1 100%;
            font-size: 13px;
        }

        h1 {
            font-size: 1.2rem;
        }
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

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <?php foreach ($cols as $col): ?>
                            <th><?= htmlspecialchars($col) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 1; ?>
                    <?php while ($row = $members->fetch_assoc()): ?>
                    <tr>
                        <td><?= $counter++ ?></td>
                        <?php foreach ($cols as $col): ?>
                            <td>
                                <?php 
                                    if ($col === 'IsCase') {
                                        echo $row[$col] == 1 ? "Yes" : "No";
                                    } else {
                                        echo htmlspecialchars($row[$col]);
                                    }
                                ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>