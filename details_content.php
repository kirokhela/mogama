<?php
session_start();

// الاتصال بقاعدة البيانات
require_once 'db.php'; // لازم يكون فيه $pdo

// عدد السجلات في كل صفحة
$records_per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int) $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;

// ==================== إجمالي الكلي بدون LIMIT ====================
$total_records_query = $pdo->query("SELECT COUNT(*) FROM employees");
$total_records = $total_records_query->fetchColumn();

$total_payment_query = $pdo->query("SELECT SUM(payment) FROM employees");
$grand_total_payment = $total_payment_query->fetchColumn();

// ==================== الاستعلام بالـ LIMIT ====================
$stmt = $pdo->prepare("SELECT * FROM employees ORDER BY id DESC LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// حساب عدد الصفحات
$total_pages = ceil($total_records / $records_per_page);
?>
<!DOCTYPE html>
<html lang="ar">
<head>
  <meta charset="UTF-8">
  <title>إدارة الطلاب</title>
  <style>
    body {
      font-family: 'Tahoma', sans-serif;
      background: #f4f6f9;
      margin: 0;
      padding: 20px;
      direction: rtl;
      text-align: center;
    }
    .container {
      max-width: 1100px;
      margin: auto;
    }
    h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .cards {
      display: flex;
      justify-content: center;
      gap: 20px;
      margin-bottom: 25px;
      flex-wrap: wrap;
    }
    .count-box {
      flex: 1;
      min-width: 220px;
      background: #ffffff;
      border-radius: 16px;
      padding: 25px 15px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
      text-align: center;
      transition: transform 0.3s, box-shadow 0.3s;
    }
    .count-box:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 22px rgba(0, 0, 0, 0.15);
    }
    .count-box h3 {
      font-size: 18px;
      margin-bottom: 10px;
      color: #555;
    }
    .count-box strong {
      font-size: 26px;
      color: #007bff;
      display: block;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      background: #fff;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
    }
    th, td {
      padding: 12px;
      border-bottom: 1px solid #eee;
    }
    th {
      background: #007bff;
      color: #fff;
      font-size: 15px;
    }
    tr:hover {
      background: #f1f7ff;
    }
    .pagination {
      margin-top: 25px;
    }
    .pagination a {
      margin: 0 5px;
      padding: 8px 14px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      transition: background 0.3s;
    }
    .pagination a:hover {
      background: #0056b3;
    }
    .pagination .current {
      background: #6c757d;
      pointer-events: none;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>لوحة إدارة الطلاب</h2>

    <!-- الكروت -->
    <div class="cards">
      <div class="count-box">
        <h3>إجمالي المسجلين</h3>
        <strong><?php echo $total_records; ?></strong>
      </div>
      <div class="count-box">
        <h3>إجمالي المدفوعات</h3>
        <strong><?php echo number_format($grand_total_payment, 2); ?></strong>
      </div>
    </div>

    <!-- الجدول -->
    <table>
      <tr>
        <th>ID</th>
        <th>الاسم</th>
        <th>الفريق</th>
        <th>المبلغ</th>
        <th>التاريخ</th>
      </tr>
      <?php foreach ($data as $row): ?>
      <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['team']); ?></td>
        <td><?php echo number_format($row['payment'], 2); ?></td>
        <td><?php echo $row['created_at']; ?></td>
      </tr>
      <?php endforeach; ?>
    </table>

    <!-- الصفحات -->
    <div class="pagination">
      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'current' : ''; ?>">
          <?php echo $i; ?>
        </a>
      <?php endfor; ?>
    </div>
  </div>
</body>
</html>