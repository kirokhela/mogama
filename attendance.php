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

// Get last update timestamp for polling
$last_update = $conn->query("SELECT MAX(GREATEST(
    COALESCE((SELECT MAX(UNIX_TIMESTAMP(Timestamp)) FROM employees), 0),
    COALESCE((SELECT MAX(UNIX_TIMESTAMP(attendance_time)) FROM Attended_employee), 0)
)) as last_update")->fetch_assoc()['last_update'];

$conn->close();

ob_start();
?>

<header>
    <img src="shamandora.png" alt="Logo" style="max-width: 80px; height: auto;">
    <h1>📋 إدارة الحضور</h1>
    <div class="status-indicator">
        <span id="connectionStatus" class="status-online">🟢 متصل</span>
        <span id="lastUpdate">آخر تحديث: الآن</span>
    </div>
</header>

<div class="buttons">
    <button class="attend" onclick="moveToAttend()">✅ تسجيل حضور</button>
    <button class="remove" onclick="removeFromAttend()">❌ إزالة</button>
    <button class="refresh" onclick="forceRefresh()">🔄 تحديث يدوي</button>
</div>

<div class="container">
    <!-- Employees -->
    <div class="table-box">
        <h3>كل الملتحقين <span id="employeeCount">(<?= count($employees) ?>)</span></h3>
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
        <h3>المسجلين في الحضور <span id="attendedCount">(<?= count($attended) ?>)</span></h3>
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
// ====== Global Variables ======
const employeesTable = document.getElementById("employeesTable");
const attendedTable = document.getElementById("attendedTable");
const btnAttend = document.querySelector("button.attend");
const btnRemove = document.querySelector("button.remove");
const searchEmployees = document.getElementById("searchEmployees");
const searchAttended = document.getElementById("searchAttended");
const connectionStatus = document.getElementById("connectionStatus");
const lastUpdateSpan = document.getElementById("lastUpdate");

let selectedEmployee = null;
let selectedAttended = null;
let lastUpdateTimestamp = <?= $last_update ?>;
let pollingInterval;
let isPolling = true;

// ====== Real-time Polling System ======
async function checkForUpdates() {
    try {
        const response = await fetch('check_updates.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                last_update: lastUpdateTimestamp
            })
        });

        const result = await response.json();

        if (result.success && result.has_updates) {
            console.log('Updates detected, refreshing data...');
            await refreshData();
            lastUpdateTimestamp = result.new_timestamp;
        }

        // Update connection status
        updateConnectionStatus(true);

    } catch (error) {
        console.error('Polling error:', error);
        updateConnectionStatus(false);
    }
}

async function refreshData() {
    try {
        const response = await fetch('get_data.php');
        const data = await response.json();

        if (data.success) {
            updateEmployeesTable(data.employees);
            updateAttendedTable(data.attended);
            updateCounts(data.employees.length, data.attended.length);
            updateLastUpdateTime();

            // Show notification
            showNotification('تم تحديث البيانات تلقائياً', 'success');
        }
    } catch (error) {
        console.error('Refresh error:', error);
        showNotification('خطأ في تحديث البيانات', 'error');
    }
}

function updateEmployeesTable(employees) {
    const tbody = employeesTable.querySelector('tbody');
    tbody.innerHTML = '';

    employees.forEach(emp => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', emp.id);
        tr.setAttribute('data-name', emp.name);
        tr.setAttribute('data-team', emp.team);
        tr.setAttribute('data-payment', emp.payment);

        tr.innerHTML = `
            <td>${emp.id}</td>
            <td>${escapeHtml(emp.name)}</td>
            <td>${escapeHtml(emp.team)}</td>
            <td>${escapeHtml(emp.payment)}</td>
            <td>${escapeHtml(emp.Timestamp)}</td>
        `;

        tbody.appendChild(tr);
    });

    // Reapply search filter if active
    const searchValue = searchEmployees.value;
    if (searchValue) {
        filterTable(employeesTable, searchValue);
    }
}

function updateAttendedTable(attended) {
    const tbody = attendedTable.querySelector('tbody');
    tbody.innerHTML = '';

    attended.forEach(att => {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', att.id);
        tr.setAttribute('data-name', att.name);

        tr.innerHTML = `
            <td>${att.id}</td>
            <td>${escapeHtml(att.name)}</td>
            <td>${escapeHtml(att.team)}</td>
            <td>${escapeHtml(att.payment_amount)}</td>
            <td>${att.attendance_time}</td>
        `;

        tbody.appendChild(tr);
    });

    // Reapply search filter if active
    const searchValue = searchAttended.value;
    if (searchValue) {
        filterTable(attendedTable, searchValue);
    }
}

function updateCounts(employeeCount, attendedCount) {
    document.getElementById('employeeCount').textContent = `(${employeeCount})`;
    document.getElementById('attendedCount').textContent = `(${attendedCount})`;
}

function updateConnectionStatus(isOnline) {
    if (isOnline) {
        connectionStatus.innerHTML = '🟢 متصل';
        connectionStatus.className = 'status-online';
    } else {
        connectionStatus.innerHTML = '🔴 غير متصل';
        connectionStatus.className = 'status-offline';
    }
}

function updateLastUpdateTime() {
    const now = new Date();
    const timeStr = now.toLocaleTimeString('ar-EG');
    lastUpdateSpan.textContent = `آخر تحديث: ${timeStr}`;
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    // Add to page
    document.body.appendChild(notification);

    // Auto remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Start polling when page loads
function startPolling() {
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(checkForUpdates, 2000); // Check every 2 seconds
    isPolling = true;
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    isPolling = false;
}

// Force refresh function
async function forceRefresh() {
    await refreshData();
}

// ====== Selection State ======
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
employeesTable.addEventListener("click", (e) => {
    const tr = e.target.closest("tr[data-id]");
    if (!tr) return;

    if (tr.classList.contains("selected")) {
        tr.classList.remove("selected");
        selectedEmployee = null;
    } else {
        clearSelection(employeesTable);
        clearSelection(attendedTable);
        selectedAttended = null;

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

    if (tr.classList.contains("selected")) {
        tr.classList.remove("selected");
        selectedAttended = null;
    } else {
        clearSelection(employeesTable);
        clearSelection(attendedTable);
        selectedEmployee = null;

        tr.classList.add("selected");
        selectedAttended = {
            id: tr.dataset.id,
            name: tr.dataset.name
        };
    }
    updateButtons();
});

// ====== Search Filter ======
function filterTable(table, query) {
    const q = query.toLowerCase();
    table.querySelectorAll("tbody tr").forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? "" : "none";
    });
}

function attachSearch(input, table) {
    input.addEventListener("input", () => {
        filterTable(table, input.value);
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
    showNotification(msg, 'info');
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

    showNotification(data.message, data.success ? 'success' : 'error');

    if (data.success) {
        // Clear selection and refresh data instead of full reload
        selectedEmployee = null;
        clearSelection(employeesTable);
        updateButtons();
        await refreshData();
    }
    btnAttend.disabled = false;
}

async function removeFromAttend() {
    if (!selectedAttended) return toast("اختر ملتحق أولا");
    btnRemove.disabled = true;
    let data = await postForm("remove_attendance.php", {
        id: selectedAttended.id
    });

    showNotification(data.message, data.success ? 'success' : 'error');

    if (data.success) {
        // Clear selection and refresh data instead of full reload
        selectedAttended = null;
        clearSelection(attendedTable);
        updateButtons();
        await refreshData();
    }
    btnRemove.disabled = false;
}

// ====== Page Visibility API ======
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopPolling();
    } else {
        startPolling();
        // Refresh data when page becomes visible again
        setTimeout(refreshData, 500);
    }
});

// Start polling when page loads
window.addEventListener('load', () => {
    startPolling();
    updateLastUpdateTime();
});

// Cleanup when page unloads
window.addEventListener('beforeunload', () => {
    stopPolling();
});
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
    position: relative;
}

.status-indicator {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 12px;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 5px;
}

.status-online {
    color: #10b981;
}

.status-offline {
    color: #ef4444;
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
    position: relative;
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

button.refresh {
    background: #3b82f6;
    color: white;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 6px;
    color: white;
    font-weight: bold;
    z-index: 1000;
    animation: slideIn 0.3s ease-out;
}

.notification-success {
    background: #10b981;
}

.notification-error {
    background: #ef4444;
}

.notification-info {
    background: #3b82f6;
}

@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }

    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .status-indicator {
        position: static;
        text-align: center;
        margin-top: 10px;
    }

    .container {
        flex-direction: column;
        padding: 10px;
    }

    .table-box {
        min-width: unset;
    }
}
</style>

<?php
$pageContent = ob_get_clean();
include "layout.php";
?>