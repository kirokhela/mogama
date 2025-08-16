<?php
// Assume you already have $data and $total_payment prepared in your parent PHP before including layout
?>

<link rel="stylesheet" href="style.css">

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

body {
    font-family: 'Cairo', sans-serif;
    background: white;
    min-height: 100vh;
    padding: 20px;
    direction: rtl;
}

.page-title {
    text-align: center;
    color: #2d3748;
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 30px;
}

.filters-container {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.filters-form {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: center;
}

.filters-form input,
.filters-form select,
.filters-form button {
    padding: 12px 16px;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    font-size: 14px;
    background: white;
}

.filters-form button {
    background: #3b82f6;
    color: white;
    border: none;
    cursor: pointer;
    font-weight: 600;
    min-width: 100px;
}

.count-box {
    background: #0f766e;
    color: white;
    padding: 12px 16px;
    border-radius: 4px;
    font-weight: 600;
    margin: 10px 0;
    font-size: 14px;
    border: none;
    height: 44px;
    display: flex;
    align-items: center;
    min-width: 100px;
}

.table-container {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    margin: 0 auto;
    max-width: 100%;
}

.table-wrapper {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
    background: white;
}

th {
    background: #f8fafc;
    color: #000;
    font-weight: 600;
    padding: 12px;
    text-align: center;
    font-size: 13px;
}

td {
    padding: 12px;
    text-align: center;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #4a5568;
}

tr:nth-child(even) {
    background: #f8fafc;
}

tr:hover {
    background: #f1f5f9;
}

tfoot td {
    font-weight: bold;
    background: #0f766e;
    color: white;
    font-size: 16px;
    padding: 15px;
}

.action-links {
    display: flex;
    gap: 5px;
    justify-content: center;
    flex-wrap: wrap;
}

.action-links a {
    padding: 6px 12px;
    font-size: 12px;
    border-radius: 20px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 500;
    white-space: nowrap;
}

.action-links a:hover {
    opacity: 0.9;
}

.action-links a.edit {
    background: #3b82f6;
    color: white;
}

.action-links a.delete {
    background: #ef4444;
    color: white;
}

.action-links a.resend {
    background: #f59e0b;
    color: white;
}

.no-records {
    background: white;
    padding: 50px 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
    margin: 0 auto;
    max-width: 500px;
}

.no-records h3 {
    color: #4a5568;
    font-size: 24px;
    margin-bottom: 15px;
}

.no-records p {
    color: #718096;
    font-size: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .main-container {
        padding: 20px;
        margin: 10px;
    }

    .page-title {
        font-size: 2rem;
    }

    .filters-form {
        flex-direction: column;
        width: 100%;
    }

    .filters-form input,
    .filters-form select,
    .filters-form button {
        width: 100%;
    }

    .table-container {
        max-width: 100%;
    }

    th,
    td {
        padding: 8px 6px;
        font-size: 12px;
    }

    .action-links {
        flex-direction: column;
        gap: 3px;
    }

    .action-links a {
        font-size: 11px;
        padding: 4px 8px;
    }
}

.pagination {
    margin: 20px auto;
    text-align: center;
}

.pagination a {
    display: inline-block;
    margin: 0 5px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
}

.pagination a:hover {
    background: #f0f0f0;
}

.pagination a.active {
    background: #007bff;
    color: #fff;
    border-color: #007bff;
}
</style>

<div class="page-title">تفاصيل الملتحقين</div>

<?php if (count($data) > 0): ?>
<div class="filters-container">
    <form method="get" class="filters-form">
        <input type="text" name="name" placeholder="البحث بالاسم..."
            value="<?php echo htmlspecialchars($_GET['name'] ?? ''); ?>">

        <select name="date">
            <option value="">فلترة بالتاريخ</option>
            <?php foreach ($dates as $d): ?>
            <option value="<?php echo $d; ?>"
                <?php if (!empty($_GET['date']) && $_GET['date'] == $d) echo 'selected'; ?>>
                <?php echo $d; ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="team">
            <option value="">فلترة بالفريق</option>
            <?php foreach ($teams as $team): ?>
            <option value="<?php echo $team; ?>"
                <?php if (!empty($_GET['team']) && $_GET['team'] == $team) echo 'selected'; ?>>
                <?php echo $team; ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select name="is_case">
            <option value="">Filter by Case</option>
            <option value="1" <?php if ($is_case_filter === '1') echo 'selected'; ?>>نعم</option>
            <option value="0" <?php if ($is_case_filter === '0') echo 'selected'; ?>>لا</option>
        </select>

        <button type="submit">تطبيق</button>
    </form>

    <div class="count-box">
        إجمالي المسجلين: <strong><?php echo count($data); ?></strong>
    </div>

    <div class="count-box">
        الإجمالي: <strong><?php echo number_format($grand_total_payment, 2); ?></strong>
    </div>
</div>

<div class="table-container">
    <div class="table-wrapper">
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
                    <th>هل حالة</th>
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
                    <td><?php echo $row["IsCase"] == 1 ? "نعم" : "لا"; ?></td>
                    <td><?php echo date("Y-m-d", strtotime($row["Timestamp"])); ?></td>
                    <?php if (is_admin()): ?>
                    <td class="action-links">
                        <a href="edit.php?id=<?php echo urlencode($row['id']); ?>" class="edit">تعديل</a>
                        <a href="delete.php?id=<?php echo urlencode($row['id']); ?>" class="delete"
                            onclick="return confirm('حذف هذا السجل؟')">حذف</a>
                        <a href="resend.php?id=<?php echo urlencode($row['id']); ?>" class="resend">إعادة إرسال</a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <!-- <tfoot>
                <tr>
                    <td colspan="<?php echo is_admin() ? 10 : 9; ?>" style="text-align: center;">
                        الإجمالي: <?php echo number_format($grand_total_payment, 2); ?></p>

                    </td>
                </tr>
            </tfoot> -->
        </table>

        <div class="pagination">
            <?php if ($page > 1): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Prev</a>
            <?php endif; ?>

            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"
                class="<?php echo $p == $page ? 'active' : ''; ?>">
                <?php echo $p; ?>
            </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): ?>
            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php else: ?>
<div class="no-records">
    <h3>لا توجد سجلات</h3>
    <p>جرب تعديل الفلاتر الخاصة بك.</p>
</div>
<?php endif; ?>