<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection settings - Update these with your actual database credentials
$host = '193.203.168.53';
$dbname = 'u968010081_mogamaa';
$username = 'u968010081_mogamaa';
$password = 'Mogamaa_2000';

try {
    // Get the JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['id']) || empty($input['id'])) {
        throw new Exception('Employee ID is required');
    }
    
    $employeeId = $input['id'];
    
    // Create PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prepare and execute query
    // Update table name and column names to match your database schema
    $stmt = $pdo->prepare("SELECT id, name, payment, team FROM employees WHERE id = :id");
    $stmt->bindParam(':id', $employeeId);
    $stmt->execute();
    
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        echo json_encode([
            'success' => true,
            'employee' => [
                'id' => $employee['id'],
                'name' => $employee['name'],
                'payment' => $employee['payment'],
                'team' => $employee['team']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found with ID: ' . $employeeId
        ]);
    }
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>