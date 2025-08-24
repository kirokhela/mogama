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

// Capture the page content
ob_start();
?>



<style>
/* Team Members Page Styles - Inline to ensure they work */
.team-header {
    text-align: center;
    color: #0f766e;
    font-size: 1.5rem;
    margin: 0 0 20px 0;
}

.action-cards {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    justify-content: center;
    width: 100%;
}

.action-btn {
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

.action-btn:hover {
    background: #0d665b;
}

.table-wrapper {
    width: 100%;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
}

.data-table {
    width: 100%;
    min-width: 600px;
    border-collapse: collapse;
    margin: 0;
}

.data-table th,
.data-table td {
    padding: 10px;
    text-align: center;
    border-bottom: 1px solid #eee;
    font-size: 14px;
    white-space: nowrap;
    vertical-align: middle;
}

.data-table th {
    background: #0f766e;
    color: #fff;
    position: sticky;
    top: 0;
    z-index: 10;
}

.data-table tr:hover {
    background: #f1f1f1;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
    .team-header {
        font-size: 1.2rem;
        margin-bottom: 12px;
        padding: 0 5px;
    }

    .action-cards {
        flex-direction: column;
        gap: 6px;
        margin-bottom: 12px;
    }

    .action-btn {
        width: 100%;
        min-width: auto;
        padding: 10px 12px;
        font-size: 13px;
    }

    .table-wrapper {
        margin-top: 8px;
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .data-table {
        min-width: 600px;
        font-size: 11px;
    }

    .data-table th,
    .data-table td {
        padding: 6px 4px;
        font-size: 11px;
        vertical-align: middle;
        text-align: center;
    }

    .data-table th {
        font-size: 10px;
        font-weight: bold;
        padding: 8px 4px;
        text-align: center;
    }

    .data-table td:nth-child(2) {
        min-width: 30px;
    }
    
    .data-table td:nth-child(5) {
        min-width: 100px;
    }
}

@media (max-width: 480px) {
    .team-header {
        font-size: 1.1rem;
        margin-bottom: 10px;
    }

    .action-btn {
        padding: 9px 10px;
        font-size: 12px;
    }

    .data-table {
        min-width: 550px;
    }

    .data-table th,
    .data-table td {
        padding: 5px 3px;
        font-size: 10px;
        text-align: center;
        vertical-align: middle;
    }

    .data-table th {
        font-size: 9px;
        padding: 7px 3px;
        text-align: center;
    }
}

@media (max-width: 360px) {
    .data-table {
        min-width: 500px;
    }

    .data-table th,
    .data-table td {
        padding: 4px 2px;
        font-size: 9px;
    }
}

@media (max-width: 768px) and (orientation: landscape) {
    .team-header {
        font-size: 1.2rem;
        margin-bottom: 10px;
    }

    .action-cards {
        flex-direction: row;
        justify-content: center;
    }

    .action-btn {
        width: auto;
        min-width: 120px;
    }
}
</style>

<div class="team-content">
    <h1 class="team-header">أعضاء فريق <?= htmlspecialchars($team) ?></h1>
    
    <div class="action-cards">
        <a href="dashboard.php" class="action-btn">⬅ رجوع للرئيسية</a>
        <a href="team_members.php?team=<?= urlencode($team) ?>&download_csv=1" class="action-btn">📥 تحميل CSV</a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
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

<?php
// Capture the content
$pageContent = ob_get_clean();

// Include the layout
include 'layout.php';
?>