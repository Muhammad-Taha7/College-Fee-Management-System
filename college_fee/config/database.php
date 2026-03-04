<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'college_fee_db');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset
$conn->set_charset("utf8mb4");

// Helper function to sanitize input
function sanitize($conn, $data) {
    return $conn->real_escape_string(htmlspecialchars(trim($data)));
}

// Generate unique student ID
function generateStudentId($conn) {
    $year = date('Y');
    $result = $conn->query("SELECT student_id FROM students ORDER BY id DESC LIMIT 1");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $num = intval(substr($row['student_id'], 7)) + 1;
    } else {
        $num = 1;
    }
    return 'STU' . $year . str_pad($num, 3, '0', STR_PAD_LEFT);
}

// Generate unique receipt number
function generateReceiptNo($conn) {
    $year = date('Y');
    $result = $conn->query("SELECT receipt_no FROM payments ORDER BY id DESC LIMIT 1");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $num = intval(substr($row['receipt_no'], 7)) + 1;
    } else {
        $num = 1;
    }
    return 'RCP' . $year . str_pad($num, 3, '0', STR_PAD_LEFT);
}
?>
