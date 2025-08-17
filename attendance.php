<?php
// =================== DB CONNECTION ===================
$servername = "193.203.168.53";
$username   = "u968010081_mogamaa";
$password   = "Mogamaa_2000";
$dbname     = "u968010081_mogamaa";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Fetch employees (not yet attended)
$employees = $conn->query("SELECT * FROM employees WHERE scan_count = 0 ORDER BY team, name")->fetch_all(MYSQLI_ASSOC);

// Fetch attended employees
$attended = $conn->query("SELECT * FROM Attended_employee ORDER BY attendance_time DESC")->fetch_all(MYSQLI_ASSOC);

$conn->close();

ob_start();
?>

<header>
    <img src="shamandora.png" alt="Logo" style="max-width: 80px; height: auto;">
    <h1>📋 إدارة الحضور</h1>

</header>

<div class="buttons">
    <button class="attend" onclick="moveToAttend()">✅ تسجيل حضور</button>
    <button class="remove" onclick="removeFromAttend()">❌ إزالة</button>
</div>

<div class="container">
    <!-- Employees -->
    <div class="table-box">
        <h3>كل الموظفين</h3>
        <div class="search-box">
            <input type="text" id="searchEmployees" placeholder="🔍 ابحث...">
        </div>
        <table id="employeesTable">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الفريق</th>
                    <th>المبلغ</th>
                    <th>وقت الحجز</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                <tr data-id="<?= $emp['id'] ?>" data-name="<?= htmlspecialchars($emp['name']) ?>"
                    data-team="<?= htmlspecialchars($emp['team']) ?>"
                    data-payment="<?= htmlspecialchars($emp['payment']) ?>">
                    <td><?= $emp['id'] ?></td>
                    <td><?= htmlspecialchars($emp['name']) ?></td>
                    <td><?= htmlspecialchars($emp['team']) ?></td>
                    <td><?= htmlspecialchars($emp['payment']) ?></td>
                    <td><?= htmlspecialchars($emp['Timestamp']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Attended -->
    <div class="table-box">
        <h3>المسجلين في الحضور</h3>
        <div class="search-box">
            <input type="text" id="searchAttended" placeholder="🔍 ابحث...">
        </div>
        <table id="attendedTable">
            <thead>
                <tr>
                    <th>الكود</th>
                    <th>الاسم</th>
                    <th>الفريق</th>
                    <th>المبلغ</th>
                    <th>وقت الحضور</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($attended as $att): ?>
                <tr data-id="<?= $att['id'] ?>" data-name="<?= htmlspecialchars($att['name']) ?>">
                    <td><?= $att['id'] ?></td>
                    <td><?= htmlspecialchars($att['name']) ?></td>
                    <td><?= htmlspecialchars($att['team']) ?></td>
                    <td><?= htmlspecialchars($att['payment_amount']) ?></td>
                    <td><?= $att['attendance_time'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// ====== Elements ======
const employeesTable = document.getElementById("employeesTable");
const attendedTable = document.getElementById("attendedTable");
const btnAttend = document.querySelector("button.attend");
const btnRemove = document.querySelector("button.remove");
const searchEmployees = document.getElementById("searchEmployees");
const searchAttended = document.getElementById("searchAttended");

// ====== Selection State ======
let selectedEmployee = null;
let selectedAttended = null;

function clearSelection(table) {
    table.querySelectorAll("tr.selected").forEach(tr => tr.classList.remove("selected"));
}

function selectRow(table, tr) {
    clearSelection(table);
    tr.classList.add("selected");
}

function updateButtons() {
    btnAttend.disabled = !selectedEmployee;
    btnRemove.disabled = !selectedAttended;
}
updateButtons();

// ====== Click Handlers ======
// ====== Click Handlers ======
employeesTable.addEventListener("click", (e) => {
    const tr = e.target.closest("tr[data-id]");
    if (!tr) return;

    // If already selected, unselect
    if (tr.classList.contains("selected")) {
        tr.classList.remove("selected");
        selectedEmployee = null;
    } else {
        // Clear both tables
        clearSelection(employeesTable);
        clearSelection(attendedTable);
        selectedAttended = null;

        // Select this row
        tr.classList.add("selected");
        selectedEmployee = {
            id: tr.dataset.id,
            name: tr.dataset.name,
            team: tr.dataset.team,
            payment: tr.dataset.payment
        };
    }
    updateButtons();
});

attendedTable.addEventListener("click", (e) => {
    const tr = e.target.closest("tr[data-id]");
    if (!tr) return;

    // If already selected, unselect
    if (tr.classList.contains("selected")) {
        tr.classList.remove("selected");
        selectedAttended = null;
    } else {
        // Clear both tables
        clearSelection(employeesTable);
        clearSelection(attendedTable);
        selectedEmployee = null;

        // Select this row
        tr.classList.add("selected");
        selectedAttended = {
            id: tr.dataset.id,
            name: tr.dataset.name
        };
    }
    updateButtons();
});

// ====== Search Filter ======
function attachSearch(input, table) {
    input.addEventListener("input", () => {
        const q = input.value.toLowerCase();
        table.querySelectorAll("tbody tr").forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? "" : "none";
        });
    });
}
attachSearch(searchEmployees, employeesTable);
attachSearch(searchAttended, attendedTable);

// ====== Helpers ======
async function postForm(url, payload) {
    const fd = new FormData();
    for (let k in payload) fd.append(k, payload[k]);
    const res = await fetch(url, {
        method: "POST",
        body: fd
    });
    return res.json().catch(() => ({
        success: false,
        message: "Unexpected server response"
    }));
}

function toast(msg) {
    alert(msg);
}

// ====== Actions ======
async function moveToAttend() {
    if (!selectedEmployee) return toast("اختر موظف أولا");
    btnAttend.disabled = true;
    let data = await postForm("add_attendance.php", {
        id: selectedEmployee.id,
        name: selectedEmployee.name,
        team: selectedEmployee.team,
        payment_amount: selectedEmployee.payment
    });
    toast(data.message);
    if (data.success) location.reload();
}


async function removeFromAttend() {
    if (!selectedAttended) return toast("اختر موظف أولا");
    btnRemove.disabled = true;
    let data = await postForm("remove_attendance.php", {
        id: selectedAttended.id
    });
    toast(data.message);
    if (data.success) location.reload();
}
</script>

<style>
body {
    font-family: "Tahoma", sans-serif;
    margin: 0;
    padding: 0;
    background: #f9f9f9;
}

header {
    color: #2c3e50;
    padding: 15px;
    text-align: center;
}

.container {
    display: flex;
    gap: 30px;
    justify-content: center;
    flex-wrap: wrap;
    padding: 20px;
}

.table-box {
    flex: 1;
    min-width: 400px;
    background: white;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}

.table-box h3 {
    text-align: center;
    margin-bottom: 15px;
    color: #333;
}

.search-box {
    margin-bottom: 10px;
    text-align: center;
}

.search-box input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    width: 90%;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th,
td {
    text-align: center;
    padding: 10px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

th {
    background: #f1f1f1;
}

tr:hover {
    background: #f9fafb;
}

tr.selected {
    background: #3b82f6;
    color: white;
}

.buttons {
    text-align: center;
    margin: 20px 0;
}

button {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin: 0 5px;
    font-weight: bold;
}

button.attend {
    background: #10b981;
    color: white;
}

button.remove {
    background: #ef4444;
    color: white;
}
</style>

<?php
$pageContent = ob_get_clean();
include "layout.php";
?>