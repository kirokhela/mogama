<?php
session_start();
require_once 'includes/auth.php'; // for is_admin()
require_once 'db.php';            // database connection ($conn)

// Check if user is admin (optional - remove if not needed)
// if (!is_admin()) {
//     header("Location: login.php");
//     exit;
// }

try {
    // Get all employees data
    $sql = "SELECT * FROM employees ORDER BY Timestamp DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Database query failed: " . $conn->error);
    }
    
    // Generate filename with current date
    $filename = 'employees_backup_' . date('Y-m-d_H-i-s') . '.xls';
    
    // Set headers for download as Excel file
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Start HTML output with proper encoding for Arabic
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    echo '<!DOCTYPE html>
    <html dir="rtl">
    <head>
        <meta charset="UTF-8">
        <style>
            table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: right; }
            th { background-color: #f2f2f2; font-weight: bold; }
            .highlight { background-color: #FFFF00; font-weight: bold; }
        </style>
    </head>
    <body>
    <table>';
    
    if ($result->num_rows > 0) {
        // Get column names from the first row
        $first_row = $result->fetch_assoc();
        $columns = array_keys($first_row);
        
        // Reset result pointer
        $result->data_seek(0);
        
        // Write headers
        echo '<tr>';
        foreach ($columns as $column) {
            echo '<th>' . htmlspecialchars($column, ENT_QUOTES, 'UTF-8') . '</th>';
        }
        echo '</tr>';
        
        // Write data rows
        while ($row = $result->fetch_assoc()) {
            // Check if scan_count column exists and equals 1
            $highlight_row = (isset($row['scan_count']) && $row['scan_count'] == 1);
            
            echo '<tr' . ($highlight_row ? ' class="highlight"' : '') . '>';
            foreach ($columns as $column) {
                $cell_value = htmlspecialchars($row[$column] ?? '', ENT_QUOTES, 'UTF-8');
                echo '<td>' . $cell_value . '</td>';
            }
            echo '</tr>';
        }
        
    } else {
        echo '<tr><td>No data found in employees table</td></tr>';
    }
    
    echo '</table>
    </body>
    </html>';
    
} catch (Exception $e) {
    // Error handling
    echo "Error: " . $e->getMessage();
    exit;
}

// Close database connection
$conn->close();
?>