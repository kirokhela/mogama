<?php
session_start();
require_once 'db.php';

$team = isset($_GET['team']) ? $conn->real_escape_string($_GET['team']) : '';
if (!$team) die("No team specified.");

// ================== CSV DOWNLOAD ==================
if (isset($_GET['download_csv'])) {
    $sql = "SELECT id, name, phone ,team ,grade, payment, IsCase 
            FROM employees 
            WHERE team = '$team'";
    $members = $conn->query($sql);

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment;filename=\"{$team}_members.csv\"");
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'NAME', 'PHONE','TEAM ','GRADE', 'PAYMENT', 'IsCase']);

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
// ================== END CSV DOWNLOAD ==================

// --- Filters ---
$name_filter = isset($_GET['name']) ? $conn->real_escape_string($_GET['name']) : '';
$is_case_filter = (isset($_GET['is_case']) && $_GET['is_case'] !== '') 
    ? $conn->real_escape_string($_GET['is_case']) 
    : '';

// --- Base query ---
$sql = "SELECT * FROM employees WHERE team = '$team'";
if ($name_filter) $sql .= " AND name LIKE '%$name_filter%'";
if ($is_case_filter !== '') $sql .= " AND IsCase = '$is_case_filter'";
$sql .= " ORDER BY id DESC";

$members = $conn->query($sql);

// --- Columns ---
$res = $conn->query("SHOW COLUMNS FROM employees");
$cols = [];
while ($c = $res->fetch_assoc()) {
    if ($c['Field'] != 'scan_count') {
        $cols[] = $c['Field'];
    }
}

// --- Prepare page content ---
$pageContent = "team_members_content.php";
include "layout.php";