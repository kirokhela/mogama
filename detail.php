<?php
session_start();
require_once 'includes/auth.php'; // for is_admin()
require_once 'db.php'; // your existing DB file ($conn)

// Handle filters
$name_filter = isset($_GET['name']) ? $conn->real_escape_string($_GET['name']) : '';
$date_filter = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
$team_filter = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
$is_case_filter = '';
if (isset($_GET['is_case']) && $_GET['is_case'] !== '') {
    $is_case_filter = $conn->real_escape_string($_GET['is_case']);
}
// Get distinct dates
$dates_result = $conn->query("SELECT DISTINCT DATE(Timestamp) as date FROM employees ORDER BY date ASC");
$dates = [];
while ($row = $dates_result->fetch_assoc()) {
    $dates[] = $row['date'];
}

// Get distinct teams
$teams_result = $conn->query("SELECT DISTINCT team FROM employees");
$teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $teams[] = $row['team'];
}

// Build query with filters
$sql = "SELECT * FROM employees WHERE 1=1";
if ($name_filter) $sql .= " AND name LIKE '%$name_filter%'";
if ($date_filter) $sql .= " AND DATE(Timestamp) = '$date_filter'";
if ($team_filter) $sql .= " AND team = '$team_filter'";
if ($is_case_filter !== '') $sql .= " AND IsCase = '$is_case_filter'";
$sql .= " ORDER BY Timestamp DESC";

$result = $conn->query($sql);

// Calculate total
$total_payment = 0;
$data = [];
while ($row = $result->fetch_assoc()) {
    $total_payment += floatval($row["payment"]);
    $data[] = $row;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <title>تفاصيل الموظفين</title>
    <style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: "Segoe UI", Arial, sans-serif;
        background: #f4f6f9;
        color: #333;
        direction: rtl;
    }

    .layout {
        display: flex;
        min-height: 100vh;
        direction: rtl;
    }

    .sidebar {
        flex-shrink: 0;
    }

    .main-content {
        flex: 1;
        padding: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin-right: 220px;
        margin-left: 0;
    }

    .content-wrapper {
        width: 100%;
        max-width: 1200px;
    }

    h1 {
        color: #0f766e;
        margin-bottom: 20px;
        text-align: center;
    }

    .filter-form {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        justify-content: center;
        margin-bottom: 20px;
        padding: 15px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
    }

    .filter-form input,
    .filter-form select {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
    }

    .filter-form input[type=submit] {
        background: #0f766e;
        color: #fff;
        border: none;
        cursor: pointer;
        transition: background 0.2s;
    }

    .filter-form input[type=submit]:hover {
        background: #115e59;
    }

    .table-container {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        overflow: auto;
        width: 100%;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 12px;
        text-align: right;
        border-bottom: 1px solid #f0f0f0;
    }

    th {
        background: #f8fafc;
        font-weight: 600;
    }

    tr:nth-child(even) {
        background: #fafafa;
    }

    tr:hover {
        background: #f1f5f9;
    }

    tfoot td {
        font-weight: bold;
        background: #0f766e;
        color: #fff;
    }

    .action-links a {
        margin-right: 8px;
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 4px;
        text-decoration: none;
        transition: background 0.2s;
    }

    .action-links a:hover {
        opacity: 0.9;
    }

    .action-links a.edit {
        background: #3b82f6;
        color: #fff;
    }

    .action-links a.delete {
        background: #ef4444;
        color: #fff;
    }

    .action-links a.resend {
        background: #f59e0b;
        color: #fff;
    }

    .no-records {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        width: 100%;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .main-content {
            max-width: 100%;
            padding: 15px;
        }

        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }

        .filter-form input,
        .filter-form select {
            width: 100%;
        }

        h1 {
            font-size: 24px;
        }

        .table-container {
            font-size: 14px;
        }

        th,
        td {
            padding: 8px;
        }
    }
    </style>
</head>

<body>
    <div class="layout">
        <div class="sidebar">
            <?php include 'sidenav.php'; ?>
        </div>
        <div class="main-content">
            <div class="content-wrapper">
                <h1>تفاصيل الملتحقين</h1>
                <form method="GET" class="filter-form">
                    <input type="text" name="name" placeholder="البحث بالاسم"
                        value="<?php echo htmlspecialchars($name_filter); ?>">
                    <select name="date">
                        <option value="">فلترة حسب التاريخ</option>
                        <?php foreach ($dates as $date): ?>
                        <option value="<?php echo htmlspecialchars($date); ?>"
                            <?php if ($date_filter == $date) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($date); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="team">
                        <option value="">فلترة حسب الفريق</option>
                        <?php foreach ($teams as $team): ?>
                        <option value="<?php echo htmlspecialchars($team); ?>"
                            <?php if ($team_filter == $team) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($team); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    
                    
                <select name="is_case">
                    <option value="">Filter by Case</option>
                    <option value="1" <?php if ($is_case_filter === '1') echo 'selected'; ?>>نعم</option>
                    <option value="0" <?php if ($is_case_filter === '0') echo 'selected'; ?>>لا</option>
                </select>
                    <input type="submit" value="تطبيق الفلاتر">
                </form>
                <?php if (count($data) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الرقم التعريفي</th>
                                <th>الاسم</th>
                                <th>الهاتف</th>
                                <th>الفريق</th>
                                <th>الصف الدراسي</th>
                                <th>المبلغ المدفوع</th>
                                    <th>هل حاله</th>
                                <th>التاريخ</th>
                                <?php if (is_admin()): ?><th>الإجراءات</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data as $i => $row): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($row["id"]); ?></td>
                                <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                <td><?php echo htmlspecialchars($row["phone"]); ?></td>
                                <td><?php echo htmlspecialchars($row["team"]); ?></td>
                                <td><?php echo htmlspecialchars($row["grade"]); ?></td>
                                <td><?php echo number_format($row["payment"], 2); ?></td>
                                       <td>
                                <?php echo $row["IsCase"] == 1 ? "نعم" : "لا"; ?>
                            </td>
                                <td><?php echo date("Y-m-d", strtotime($row["Timestamp"])); ?></td>
                                <?php if (is_admin()): ?>
                                <td class="action-links">
                                    <a href="edit.php?id=<?php echo urlencode($row['id']); ?>" class="edit">Edit</a>
                                    <a href="delete.php?id=<?php echo urlencode($row['id']); ?>" class="delete"
                                        onclick="return confirm('Delete this record?')">Delete</a>
                                    <a href="resend.php?id=<?php echo urlencode($row['id']); ?>"
                                        class="resend">Resend</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                          <tr>
                              
                            <td colspan="<?php echo is_admin() ? 10 : 9; ?>"> total:
                                <?php echo number_format($total_payment, 2); ?></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <?php else: ?>
                <div class="no-records">
                    <h3>لا توجد سجلات</h3>
                    <p>جرب تعديل الفلاتر الخاصة بك.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>