<?php
// edit.php

require_once 'db.php';

// --- Get member data ---
$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM employees WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تعديل البيانات</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        * {
            box-sizing: border-box;
            font-family: 'Cairo', sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #444;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        input:focus, select:focus {
            border-color: #007bff;
            outline: none;
        }
        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        button:hover {
            background: #0056b3;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>تعديل بيانات العضو</h2>
    <form action="update.php" method="post">
        <input type="hidden" name="id" value="<?php echo $employee['id']; ?>">

        <div class="form-group">
            <label>الاسم</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
        </div>

        <div class="form-group">
            <label>الفريق</label>
            <select name="team" id="team" required>
                <option value="">اختر الفريق</option>
                <option value="براعم" <?php if($employee['team']=="براعم") echo "selected"; ?>>براعم</option>
                <option value="أشبال" <?php if($employee['team']=="أشبال") echo "selected"; ?>>أشبال</option>
                <option value="زهرات" <?php if($employee['team']=="زهرات") echo "selected"; ?>>زهرات</option>
                <option value="كشافة" <?php if($employee['team']=="كشافة") echo "selected"; ?>>كشافة</option>
                <option value="مرشدات" <?php if($employee['team']=="مرشدات") echo "selected"; ?>>مرشدات</option>
                <option value="متقدم" <?php if($employee['team']=="متقدم") echo "selected"; ?>>متقدم</option>
                <option value="رائدات" <?php if($employee['team']=="رائدات") echo "selected"; ?>>رائدات</option>
                <option value="جوالة" <?php if($employee['team']=="جوالة") echo "selected"; ?>>جوالة</option>
                <option value="قادة" <?php if($employee['team']=="قادة") echo "selected"; ?>>قادة</option>
            </select>
        </div>

        <div class="form-group">
            <label>الصف</label>
            <select name="grade" id="grade" required>
                <option value="">اختر الصف</option>
            </select>
        </div>

        <div class="form-group">
            <label>الهاتف</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
        </div>

        <button type="submit">تحديث البيانات</button>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const teamSelect = document.getElementById("team");
    const gradeSelect = document.getElementById("grade");

    function updateGrades(selectedTeam, currentGrade = "") {
        let grades = [];
        switch (selectedTeam) {
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

        gradeSelect.innerHTML = '<option value="">اختر الصف</option>';
        grades.forEach(g => {
            const option = document.createElement("option");
            option.value = g.value;
            option.textContent = g.text;
            if (g.value === currentGrade) option.selected = true;
            gradeSelect.appendChild(option);
        });
    }

    // Load grades on page load
    updateGrades("<?php echo $employee['team']; ?>", "<?php echo $employee['grade']; ?>");

    // Change grades when team changes
    teamSelect.addEventListener("change", function () {
        updateGrades(this.value);
    });
});
</script>

</body>
</html>