<?php
session_start();
require_once 'db.php';

// --- Team filter from URL ---
$team = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
if (!$team) die("No team specified.");

// --- CSV DOWNLOAD ---
if (isset($_GET['download_csv'])) {
    $sql = "SELECT id, name, phone, team, grade, payment, IsCase 
            FROM employees 
            WHERE team = '$team'";
    $members = $conn->query($sql);

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"{$team}_members.csv\"");
    echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'NAME', 'PHONE', 'TEAM', 'GRADE', 'PAYMENT', 'IsCase']);

    while ($row = $members->fetch_assoc()) {
        $isCase = $row['IsCase'] == 1 ? "Yes" : "No";
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['phone'],
            $row['team'],
            $row['grade'],
            $row['payment'],
            $isCase
        ]);
    }
    fclose($output);
    exit;
}

// --- Pagination setup ---
$limit = 50; // show 50 members per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Count total rows + sum ---
$count_sql = "SELECT COUNT(*) as total, SUM(payment) as total_payment 
              FROM employees 
              WHERE team = '$team'";
$count_result = $conn->query($count_sql);
$row_count = $count_result->fetch_assoc();
$total_rows = $row_count['total'];
$total_payment = $row_count['total_payment'] ?? 0;

$total_pages = ceil($total_rows / $limit);

// --- Get members with pagination ---
$sql = "SELECT * FROM employees WHERE team = '$team' ORDER BY id ASC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

// --- Collect members ---
$members = [];
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

// --- Get column names (except scan_count) ---
$res = $conn->query("SHOW COLUMNS FROM employees");
$cols = [];
while ($c = $res->fetch_assoc()) {
    if ($c['Field'] != 'scan_count') {
        $cols[] = $c['Field'];
    }
}

// --- Tell layout which content to load ---
$pageContent = "team_members_content.php";
include "layout.php";