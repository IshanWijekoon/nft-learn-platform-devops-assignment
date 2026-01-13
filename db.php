<?php
$DB_HOST = 'sql12.freesqldatabase.com';
$DB_USER = 'sql12814236';
$DB_PASS = 'A8MhYZy1w';
$DB_NAME = 'sql12814236';
$DB_PORT = 3306;

// Suppress warnings for cleaner JSON responses
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);

if ($conn->connect_error) {
    error_log('Database connection error: ' . $conn->connect_error);
    if (headers_sent() === false) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit();
}

$conn->set_charset('utf8mb4');
?>
