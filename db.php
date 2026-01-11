<?php
// Update credentials if needed
$DB_HOST = '127.0.0.1';
$DB_USER = 'root';
$DB_PASS = ''; // default XAMPP root password is empty
$DB_NAME = 'nft_learning';

// Suppress warnings for cleaner JSON responses
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    // Don't use die() as it outputs text - use exit() with error log instead
    error_log('Database connection error: ' . $conn->connect_error);
    if (headers_sent() === false) {
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}
$conn->set_charset('utf8mb4');
?>