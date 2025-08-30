<?php
require_once 'includes/auth.php';
require_admin();
require_once 'db.php';

$id = $_GET['id'] ?? '';
if (!$id) {
    header('Location: detail.php');
    exit;
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name       = trim($_POST['name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $team       = trim($_POST['team'] ?? '');
    $grade      = trim($_POST['grade'] ?? '');
    $payment    = trim($_POST['payment'] ?? '');
    $isCase     = isset($_POST['isCase']) ? 1 : 0;
    $new_date   = trim($_POST['date_only'] ?? '');

    // Keep old time, only change date
    $stmt = $conn->prepare("SELECT timestamp FROM employees WHERE id=? LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row_old = $res->fetch_assoc();
    $stmt->close();

    $old_time = date('H:i:s', strtotime($row_old['timestamp']));
    $new_timestamp = $new_date . ' ' . $old_time;

    // Update
    $stmt = $conn->prepare("UPDATE employees 
        SET name=?, phone=?, team=?, grade=?, payment=?, IsCase=?, timestamp=? 
        WHERE id=?");
    $stmt->bind_param("ssssssis", $name, $phone, $team, $grade, $payment, $isCase, $new_timestamp, $id);

    if ($stmt->execute()) {
        $stmt->close();
        header('Location: detail.php');
        exit;
    } else {
        $error = $stmt->error;
        $stmt->close();
    }
}

// Fetch employee
$stmt = $conn->prepare("SELECT id, name, phone, team, grade, payment, IsCase, timestamp 
                        FROM employees WHERE id=? LIMIT 1");
$stmt->bind_param("s", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    $stmt->close();
    header('Location: detail.php');
    exit;
}
$row = $res->fetch_assoc();
$stmt->close();

$current_date = date('Y-m-d', strtotime($row['timestamp']));
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<title>تعديل - <?php echo htmlspecialchars($row['name']); ?></title>
<style>
body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f4f6f9;
    padding: 20px;
    color: #333;
}
.form-box {
    max-width: 650px;
    margin: 0 auto;
    background: #fff;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,.08);
}
h3 {
    color: #0f766e;
    margin-bottom: 20px;
}
label {
    display: block;
    margin-top: 15px;
    font-weight: 500;
}
input, select {
    width: 100%;
    padding: 10px 12px;
    margin-top: 6px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
}
input[type="checkbox"] {
    width: auto;
    margin-top: 10px;
}
button {
    margin-top: 20px;
    padding: 10px 18px;
    background: #0f766e;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
}
button:hover {
    background: #115e59;
}
a.cancel {
    display: inline-block;
    margin-left: 15px;
    margin-top: 20px;
    text-decoration: none;
    color: #555;
    font-weight: 500;
    transition: color 0.2s;
}
a.cancel:hover {
    color: #0f766e;
}
.error {
    background: #fee2e2;
    color: #b91c1c;
    padding: 10px 12px;
    border-radius: 6px;
    margin-bottom: 15px;
}
</style>
</head>
<body>
<div class="form-box">
  <h3>تعديل: <?php echo htmlspecialchars($row['name']); ?></h3>
  <?php if (!empty($error)): ?>
    <div class="error"><?php echo htmlspecialchars($error); ?></div>
  <?php endif; ?>
  <form method="post">
    <label>الاسم</label>
    <input name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
    
    <label>الهاتف</label>
    <input name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
    
    <label>الفريق</label>
    <select id="team" name="team" required>
        <option value="">اختر الفريق</option>
        <option value="براعم"   <?php echo $row['team']=="براعم"?"selected":""; ?>>براعم</option>
        <option value="أشبال"   <?php echo $row['team']=="أشبال"?"selected":""; ?>>أشبال</option>
        <option value="زهرات"   <?php echo $row['team']=="زهرات"?"selected":""; ?>>زهرات</option>
        <option value="كشافة"   <?php echo $row['team']=="كشافة"?"selected":""; ?>>كشافة</option>
        <option value="مرشدات"  <?php echo $row['team']=="مرشدات"?"selected":""; ?>>مرشدات</option>
        <option value="متقدم"   <?php echo $row['team']=="متقدم"?"selected":""; ?>>متقدم</option>
        <option value="رائدات"  <?php echo $row['team']=="رائدات"?"selected":""; ?>>رائدات</option>
        <option value="جوالة"   <?php echo $row['team']=="جوالة"?"selected":""; ?>>جوالة</option>
        <option value="قادة"    <?php echo $row['team']=="قادة"?"selected":""; ?>>قادة</option>
    </select>
    
    <label>المرحلة</label>
    <select id="grade" name="grade" required></select>
    
    <label>المبلغ</label>
    <input name="payment" value="<?php echo htmlspecialchars($row['payment']); ?>" required>
    
    <label>تاريخ التسجيل</label>
    <input type="date" name="date_only" value="<?php echo $current_date; ?>" required>
    
    <label>
      <input type="checkbox" name="isCase" value="1" <?php echo $row['IsCase'] ? 'checked' : ''; ?>>
      حالة خاصة
    </label>
    
    <button type="submit">حفظ</button>
    <a href="detail.php" class="cancel">إلغاء</a>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let teamSelect = document.getElementById("team");
    let gradeSelect = document.getElementById("grade");
    let currentGrade = "<?php echo htmlspecialchars($row['grade']); ?>";

    function updateGrades() {
        let team = teamSelect.value;
        let grades = [];

        switch (team) {
            case "براعم":
                grades = [
                    {value: "اولي ابتدائي", text: "أولي ابتدائي"},
                    {value: "ثانيه ابتدائي", text: "ثانية ابتدائي"}
                ];
                break;
            case "أشبال":
            case "زهرات":
                grades = [
                    {value: "ثالثة ابتدائي", text: "ثالثة ابتدائي"},
                    {value: "رابعه ابتدائي", text: "رابعة ابتدائي"},
                    {value: "خامسه ابتدائي", text: "خامسة ابتدائي"},
                    {value: "سادسه ابتدائي", text: "سادسة ابتدائي"}
                ];
                break;
            case "كشافة":
            case "مرشدات":
                grades = [
                    {value: "اولي اعدادي", text: "أولي إعدادي"},
                    {value: "ثانيه اعدادي", text: "ثانية إعدادي"},
                    {value: "ثالثة اعدادي", text: "ثالثة إعدادي"}
                ];
                break;
            case "متقدم":
            case "رائدات":
                grades = [
                    {value: "اولي ثانوي", text: "أولي ثانوي"},
                    {value: "ثانيه ثانوي", text: "ثانية ثانوي"},
                    {value: "ثالثة ثانوي", text: "ثالثة ثانوي"}
                ];
                break;
            case "جوالة":
            case "قادة":
                grades = [
                    {value: "جامعة", text: "جامعة"},
                    {value: "خريج", text: "خريج"}
                ];
                break;
        }

        gradeSelect.innerHTML = "";
        grades.forEach(g => {
            let opt = document.createElement("option");
            opt.value = g.value;
            opt.textContent = g.text;
            if (g.value === currentGrade) opt.selected = true;
            gradeSelect.appendChild(opt);
        });
    }

    teamSelect.addEventListener("change", updateGrades);
    updateGrades();
});
</script>
</body>
</html>