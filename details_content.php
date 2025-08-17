<?php
session_start();
require_once 'db.php'; // اتصال قاعدة البيانات

// ============================
// الفلاتر
// ============================
$team_filter = isset($_GET['team']) ? $_GET['team'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$is_case_filter = isset($_GET['is_case']) ? $_GET['is_case'] : '';

// ============================
// إجمالي المسجلين + إجمالي المدفوعات (من كل الجدول)
// ============================
$total_records_query = $pdo->query("SELECT COUNT(*) FROM employees");
$total_records = $total_records_query->fetchColumn();

$total_payment_query = $pdo->query("SELECT SUM(payment) FROM employees");
$grand_total_payment = $total_payment_query->fetchColumn();

// ============================
// Pagination
// ============================
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// ============================
// استعلام البيانات
// ============================
$sql = "SELECT * FROM employees WHERE 1=1";
$params = [];

if ($team_filter != '') {
    $sql .= " AND team = :team";
    $params[':team'] = $team_filter;
}

if ($date_filter != '') {
    $sql .= " AND DATE(created_at) = :date";
    $params[':date'] = $date_filter;
}

if ($is_case_filter != '') {
    $sql .= " AND is_case = :is_case";
    $params[':is_case'] = $is_case_filter;
}

$sql .= " ORDER BY id DESC LIMIT :start, :limit";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => &$val) {
    $stmt->bindParam($key, $val);
}
$stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
$stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================
// إجمالي الصفوف للصفحات (حسب الفلاتر)
// ============================
$count_sql = "SELECT COUNT(*) FROM employees WHERE 1=1";
$count_params = [];

if ($team_filter != '') {
    $count_sql .= " AND team = :team";
    $count_params[':team'] = $team_filter;
}

if ($date_filter != '') {
    $count_sql .= " AND DATE(created_at) = :date";
    $count_params[':date'] = $date_filter;
}

if ($is_case_filter != '') {
    $count_sql .= " AND is_case = :is_case";
    $count_params[':is_case'] = $is_case_filter;
}

$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($count_params);
$total_filtered_records = $count_stmt->fetchColumn();
$total_pages = ceil($total_filtered_records / $limit);
?>

<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>لوحة التحكم</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; padding: 20px; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .count-box {
            flex: 1;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: #fff;
            padding: 25px;
            border-radius: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0 6px 15px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        }
        .count-box strong { display: block; margin-top: 8px; font-size: 28px; color: #ffeb3b; }
        .count-box:hover { transform: translateY(-5px); }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 10px; overflow: hidden; }
        table th, table td { padding: 12px; border: 1px solid #ddd; text-align: center; }
        table th { background: #357abd; color: #fff; }
        .pagination { margin: 20px 0; text-align: center; }
        .pagination a {
            display: inline-block;
            padding: 8px 12px;
            margin: 0 4px;
            background: #4a90e2;
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
        }
        .pagination a.active { background: #357abd; }
    </style>
</head>
<body>

    <!-- Cards -->
    <div class="stats">
        <div class="count-box">
            إجمالي المسجلين:
            <strong><?php echo $total_records; ?></strong>
        </div>
        <div class="count-box">
            إجمالي المدفوعات:
            <strong><?php echo number_format($grand_total_payment, 2); ?></strong>
        </div>
    </div>

    <!-- جدول البيانات -->
    <table>
        <tr>
            <th>ID</th>
            <th>الاسم</th>
            <th>الفريق</th>
            <th>المبلغ المدفوع</th>
            <th>تاريخ التسجيل</th>
            <th>حالة</th>
        </tr>
        <?php foreach ($data as $row): ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td><?= htmlspecialchars($row['team']); ?></td>
            <td><?= number_format($row['payment'], 2); ?></td>
            <td><?= $row['created_at']; ?></td>
            <td><?= $row['is_case'] ? 'نعم' : 'لا'; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i; ?><?= $team_filter ? '&team=' . $team_filter : ''; ?><?= $date_filter ? '&date=' . $date_filter : ''; ?><?= $is_case_filter ? '&is_case=' . $is_case_filter : ''; ?>"
               class="<?= $i == $page ? 'active' : ''; ?>">
                <?= $i; ?>
            </a>
        <?php endfor; ?>
    </div>

</body>
</html>