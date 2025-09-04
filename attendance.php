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
        <div class="table-container">
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
        <div class="pagination-container">
            <div class="pagination" id="employeesPagination"></div>
            <div class="pagination-info" id="employeesInfo"></div>
        </div>
    </div>

    <!-- Attended -->
    <div class="table-box">
        <h3>المسجلين في الحضور <span id="attendedCount">(<?= count($attended) ?>)</span></h3>
        <div class="search-box">
            <input type="text" id="searchAttended" placeholder="🔍 ابحث...">
        </div>
        <div class="table-container">
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
        <div class="pagination-container">
            <div class="pagination" id="attendedPagination"></div>
            <div class="pagination-info" id="attendedInfo"></div>
        </div>
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

// Pagination variables
let employeesData = [];
let attendedData = [];
let filteredEmployeesData = [];
let filteredAttendedData = [];
let currentEmployeesPage = 1;
let currentAttendedPage = 1;

// Detect device type for pagination size
function isMobile() {
    return window.innerWidth <= 768;
}

function getRowsPerPage() {
    return isMobile() ? 10 : 20;
}

// ====== Pagination System ======
class TablePagination {
    constructor(tableId, paginationId, infoId) {
        this.table = document.getElementById(tableId);
        this.paginationContainer = document.getElementById(paginationId);
        this.infoContainer = document.getElementById(infoId);
        this.currentPage = 1;
        this.data = [];
        this.filteredData = [];
        this.rowsPerPage = getRowsPerPage();
    }

    setData(data) {
        this.data = [...data];
        this.filteredData = [...data];
        this.currentPage = 1;
        this.render();
    }

    filter(query) {
        const q = query.toLowerCase();
        if (!q) {
            this.filteredData = [...this.data];
        } else {
            this.filteredData = this.data.filter(item => {
                return Object.values(item).some(val =>
                    val.toString().toLowerCase().includes(q)
                );
            });
        }
        this.currentPage = 1;
        this.render();
    }

    render() {
        this.rowsPerPage = getRowsPerPage();
        const totalPages = Math.ceil(this.filteredData.length / this.rowsPerPage);
        const startIndex = (this.currentPage - 1) * this.rowsPerPage;
        const endIndex = startIndex + this.rowsPerPage;
        const pageData = this.filteredData.slice(startIndex, endIndex);

        this.renderTable(pageData);
        this.renderPagination(totalPages);
        this.renderInfo(startIndex, endIndex);
    }

    renderTable(pageData) {
        const tbody = this.table.querySelector('tbody');
        tbody.innerHTML = '';

        pageData.forEach(item => {
            const tr = document.createElement('tr');

            // Set data attributes based on table type
            if (this.table.id === 'employeesTable') {
                tr.setAttribute('data-id', item.id);
                tr.setAttribute('data-name', item.name);
                tr.setAttribute('data-team', item.team);
                tr.setAttribute('data-payment', item.payment);

                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${escapeHtml(item.team)}</td>
                    <td>${escapeHtml(item.payment)}</td>
                    <td>${escapeHtml(item.Timestamp)}</td>
                `;
            } else {
                tr.setAttribute('data-id', item.id);
                tr.setAttribute('data-name', item.name);

                tr.innerHTML = `
                    <td>${item.id}</td>
                    <td>${escapeHtml(item.name)}</td>
                    <td>${escapeHtml(item.team)}</td>
                    <td>${escapeHtml(item.payment_amount)}</td>
                    <td>${item.attendance_time}</td>
                `;
            }

            tbody.appendChild(tr);
        });
    }

    renderPagination(totalPages) {
        if (totalPages <= 1) {
            this.paginationContainer.innerHTML = '';
            return;
        }

        let paginationHTML = '';

        // Previous button
        paginationHTML +=
            `<button class="page-btn" ${this.currentPage === 1 ? 'disabled' : ''} onclick="this.parentElement.pagination.goToPage(${this.currentPage - 1})">‹</button>`;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= this.currentPage - 2 && i <= this.currentPage + 2)) {
                paginationHTML +=
                    `<button class="page-btn ${i === this.currentPage ? 'active' : ''}" onclick="this.parentElement.pagination.goToPage(${i})">${i}</button>`;
            } else if (i === this.currentPage - 3 || i === this.currentPage + 3) {
                paginationHTML += `<span class="page-dots">...</span>`;
            }
        }

        // Next button
        paginationHTML +=
            `<button class="page-btn" ${this.currentPage === totalPages ? 'disabled' : ''} onclick="this.parentElement.pagination.goToPage(${this.currentPage + 1})">›</button>`;

        this.paginationContainer.innerHTML = paginationHTML;
        this.paginationContainer.pagination = this;
    }

    renderInfo(startIndex, endIndex) {
        const actualEnd = Math.min(endIndex, this.filteredData.length);
        const total = this.filteredData.length;

        if (total === 0) {
            this.infoContainer.innerHTML = 'لا توجد نتائج';
        } else {
            this.infoContainer.innerHTML = `عرض ${startIndex + 1}-${actualEnd} من ${total}`;
        }
    }

    goToPage(page) {
        const totalPages = Math.ceil(this.filteredData.length / this.rowsPerPage);
        if (page >= 1 && page <= totalPages) {
            this.currentPage = page;
            this.render();
        }
    }
}

// Initialize pagination instances
const employeesPagination = new TablePagination('employeesTable', 'employeesPagination', 'employeesInfo');
const attendedPagination = new TablePagination('attendedTable', 'attendedPagination', 'attendedInfo');

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
            employeesData = data.employees;
            attendedData = data.attended;

            employeesPagination.setData(employeesData);
            attendedPagination.setData(attendedData);

            updateCounts(employeesData.length, attendedData.length);
            updateLastUpdateTime();

            // Reapply current search filters
            const employeesQuery = searchEmployees.value;
            const attendedQuery = searchAttended.value;

            if (employeesQuery) {
                employeesPagination.filter(employeesQuery);
            }
            if (attendedQuery) {
                attendedPagination.filter(attendedQuery);
            }

            showNotification('تم تحديث البيانات تلقائياً', 'success');
        }
    } catch (error) {
        console.error('Refresh error:', error);
        showNotification('خطأ في تحديث البيانات', 'error');
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
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;

    document.body.appendChild(notification);

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
    pollingInterval = setInterval(checkForUpdates, 2000);
    isPolling = true;
}

function stopPolling() {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
    isPolling = false;
}

async function forceRefresh() {
    await refreshData();
}

// ====== Selection State ======
function clearSelection(table) {
    table.querySelectorAll("tr.selected").forEach(tr => tr.classList.remove("selected"));
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

// ====== Search Handlers ======
searchEmployees.addEventListener("input", (e) => {
    employeesPagination.filter(e.target.value);
});

searchAttended.addEventListener("input", (e) => {
    attendedPagination.filter(e.target.value);
});

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
        selectedAttended = null;
        clearSelection(attendedTable);
        updateButtons();
        await refreshData();
    }
    btnRemove.disabled = false;
}

// ====== Responsive handling ======
window.addEventListener('resize', () => {
    employeesPagination.render();
    attendedPagination.render();
});

// ====== Page Visibility API ======
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopPolling();
    } else {
        startPolling();
        setTimeout(refreshData, 500);
    }
});

// Initialize data when page loads
window.addEventListener('load', () => {
    // Convert PHP data to JavaScript arrays
    employeesData = <?= json_encode($employees) ?>;
    attendedData = <?= json_encode($attended) ?>;

    // Initialize pagination
    employeesPagination.setData(employeesData);
    attendedPagination.setData(attendedData);

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

.table-container {
    max-height: 60vh;
    overflow-y: auto;
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
    position: sticky;
    top: 0;
    z-index: 10;
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

/* Pagination Styles */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
    flex-wrap: wrap;
    gap: 10px;
}

.pagination {
    display: flex;
    gap: 5px;
    align-items: center;
}

.page-btn {
    padding: 8px 12px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    min-width: 40px;
}

.page-btn:hover:not(:disabled) {
    background: #f0f0f0;
}

.page-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.page-dots {
    padding: 8px 4px;
    color: #666;
}

.pagination-info {
    font-size: 14px;
    color: #666;
    white-space: nowrap;
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
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 12px;
    }

    .container {
        flex-direction: column;
        padding: 10px;
    }

    .table-box {
        min-width: unset;
    }

    .pagination-container {
        justify-content: center;
        text-align: center;
    }

    .pagination {
        order: 2;
    }

    .pagination-info {
        order: 1;
        width: 100%;
        text-align: center;
        margin-bottom: 10px;
    }

    .page-btn {
        padding: 6px 10px;
        font-size: 12px;
        min-width: 35px;
    }

    th,
    td {
        padding: 8px 5px;
        font-size: 14px;
    }
}
</style>

<?php
$pageContent = ob_get_clean();
include "layout.php";
?>