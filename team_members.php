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