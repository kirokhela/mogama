<?php
session_start();
require_once 'db.php';

$team = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
if (!$team) die("No team specified.");

// ================== CSV DOWNLOAD ==================
if (isset($_GET['download_csv'])) {
    $sql = "SELECT id, name, phone ,team ,grade, payment, IsCase 
            FROM employees 
            WHERE team = '$team'";
    $members = $conn->query($sql);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment;filename="'.$team.'_members.csv"');
    echo "\xEF\xBB\xBF"; 

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'NAME', 'PHONE','TEAM ','GRADE', 'PAYMENT', 'IsCase']);

    while ($row = $members->fetch_assoc()) {
        $isCase = $row['IsCase'] == 1 ? "Yes" : "No";
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['phone'],
            $row['team'],
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
        padding: 0;
    }

    /* Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        right: 0;
        width: 220px;
        height: 100vh;
        background: #1f2937;
        color: #fff;
        padding: 20px;
        box-sizing: border-box;
        overflow-y: auto;
        z-index: 1000;
    }

    .sidebar .logo {
        text-align: center;
        margin-bottom: 30px;
        font-size: 1.2rem;
        font-weight: bold;
        color: #10b981;
    }

    .sidebar .nav-item {
        display: block;
        padding: 12px 15px;
        color: #d1d5db;
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 5px;
        transition: all 0.3s;
    }

    .sidebar .nav-item:hover {
        background: #374151;
        color: #fff;
    }

    .sidebar .nav-item.active {
        background: #10b981;
        color: #fff;
    }

    /* Main Content */
    .main-content {
        margin-right: 220px;
        padding: 20px;
        min-height: 100vh;
    }

    h1 {
        text-align: center;
        color: #0f766e;
        font-size: 1.5rem;
        margin: 0 0 20px 0;
    }

    .cards {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
        justify-content: center;
        width: 100%;
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
        white-space: nowrap;
    }

    .btn:hover {
        background: #0d665b;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    table {
        width: 100%;
        min-width: 600px;
        border-collapse: collapse;
        margin: 0;
    }

    table th,
    table td {
        padding: 10px;
        text-align: center;
        border-bottom: 1px solid #eee;
        font-size: 14px;
        white-space: nowrap;
    }

    table th {
        background: #0f766e;
        color: #fff;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    tr:hover {
        background: #f1f1f1;
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
        display: none;
        position: fixed;
        top: 15px;
        right: 15px;
        background: #0f766e;
        color: #fff;
        border: none;
        padding: 10px;
        border-radius: 6px;
        font-size: 18px;
        z-index: 1001;
        cursor: pointer;
    }

    /* Mobile Responsive Design */
    @media (max-width: 768px) {
        /* Show mobile menu toggle */
        .mobile-menu-toggle {
            display: block;
        }

        /* Hide sidebar by default on mobile */
        .sidebar {
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        /* Show sidebar when active */
        .sidebar.active {
            transform: translateX(0);
        }

        /* Adjust main content */
        .main-content {
            margin-right: 0;
            padding: 60px 8px 8px 8px;
        }

        h1 {
            font-size: 1.2rem;
            margin-bottom: 12px;
            padding: 0 5px;
        }

        .cards {
            flex-direction: column;
            gap: 6px;
            margin-bottom: 12px;
        }

        .btn {
            width: 100%;
            min-width: auto;
            padding: 10px 12px;
            font-size: 13px;
        }

        .table-container {
            margin-top: 8px;
            border-radius: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        table {
            min-width: 600px;
            font-size: 11px;
        }

        table th,
        table td {
            padding: 6px 4px;
            font-size: 11px;
            vertical-align: middle;
        }

        table th {
            font-size: 10px;
            font-weight: bold;
            padding: 8px 4px;
        }

        /* Make specific columns more readable */
        table td:nth-child(2) { /* ID column */
            min-width: 30px;
        }
        
        table td:nth-child(6) { /* Name column */
            min-width: 120px;
            text-align: right;
        }
        
        table td:nth-child(5) { /* Phone column */
            min-width: 100px;
        }
    }

    @media (max-width: 480px) {
        .main-content {
            padding: 60px 6px 6px 6px;
        }

        h1 {
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .btn {
            padding: 9px 10px;
            font-size: 12px;
        }

        table {
            min-width: 550px;
        }

        table th,
        table td {
            padding: 5px 3px;
            font-size: 10px;
        }

        table th {
            font-size: 9px;
            padding: 7px 3px;
        }

        /* Better text alignment for Arabic names */
        table td:nth-child(6) {
            text-align: right;
            padding-right: 8px;
        }
    }

    /* Very small screens */
    @media (max-width: 360px) {
        table {
            min-width: 500px;
        }

        table th,
        table td {
            padding: 4px 2px;
            font-size: 9px;
        }
    }

    /* Landscape orientation on mobile */
    @media (max-width: 768px) and (orientation: landscape) {
        .main-content {
            padding: 60px 8px 8px 8px;
        }

        h1 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .cards {
            flex-direction: row;
            justify-content: center;
        }

        .btn {
            width: auto;
            min-width: 120px;
        }
    }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">☰</button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">نظام الإدارة</div>
        <a href="dashboard.php" class="nav-item">🏠 الرئيسية</a>
        <a href="employees.php" class="nav-item">👥 الموظفين</a>
        <a href="teams.php" class="nav-item">🏆 الفرق</a>
        <a href="reports.php" class="nav-item">📊 التقارير</a>
        <a href="settings.php" class="nav-item">⚙️ الإعدادات</a>
        <a href="logout.php" class="nav-item">🚪 تسجيل الخروج</a>
    </div>

    <!-- Main Content -->
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

    <script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('active');
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const toggle = document.querySelector('.mobile-menu-toggle');
        
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
    </script>
</body>

</html>