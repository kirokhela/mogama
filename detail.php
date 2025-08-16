<?php
session_start();
require_once 'includes/auth.php'; // for is_admin()
require_once 'db.php';            // database connection ($conn)

// --- Handle filters ---
$name_filter = isset($_GET['name']) ? $conn->real_escape_string($_GET['name']) : '';
$date_filter = isset($_GET['date']) ? $conn->real_escape_string($_GET['date']) : '';
$team_filter = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
$is_case_filter = '';
if (isset($_GET['is_case']) && $_GET['is_case'] !== '') {
    $is_case_filter = $conn->real_escape_string($_GET['is_case']);
}

// --- Get distinct dates ---
$dates_result = $conn->query("SELECT DISTINCT DATE(Timestamp) as date FROM employees ORDER BY date ASC");
$dates = [];
while ($row = $dates_result->fetch_assoc()) {
    $dates[] = $row['date'];
}

// --- Get distinct teams ---
$teams_result = $conn->query("SELECT DISTINCT team FROM employees");
$teams = [];
while ($row = $teams_result->fetch_assoc()) {
    $teams[] = $row['team'];
}

// --- Pagination setup ---
$limit = 50; // 50 per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Count total rows + grand total ---
$count_sql = "SELECT COUNT(*) as total, SUM(payment) as grand_total FROM employees WHERE 1=1";
if ($name_filter) $count_sql .= " AND name LIKE '%$name_filter%'";
if ($date_filter) $count_sql .= " AND DATE(Timestamp) = '$date_filter'";
if ($team_filter) $count_sql .= " AND team = '$team_filter'";
if ($is_case_filter !== '') $count_sql .= " AND IsCase = '$is_case_filter'";

$count_result = $conn->query($count_sql);
$row_count = $count_result->fetch_assoc();
$total_rows = $row_count['total'];
$grand_total_payment = $row_count['grand_total'] ?? 0;

$total_pages = ceil($total_rows / $limit);

// --- Build query with LIMIT + OFFSET ---
$sql = "SELECT * FROM employees WHERE 1=1";
if ($name_filter) $sql .= " AND name LIKE '%$name_filter%'";
if ($date_filter) $sql .= " AND DATE(Timestamp) = '$date_filter'";
if ($team_filter) $sql .= " AND team = '$team_filter'";
if ($is_case_filter !== '') $sql .= " AND IsCase = '$is_case_filter'";
$sql .= " ORDER BY Timestamp DESC LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

// --- Prepare data for this page ---
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// --- Tell layout which content file to use ---
$pageContent = "details_content.php";
include "layout.php";