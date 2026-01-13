<?php
$DB_HOST = 'sql12.freesqldatabase.com';
$DB_USER = 'sql12814258';
$DB_PASS = 'rAdWn25WHK';
$DB_NAME = 'sql12814258';
$DB_PORT = 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    error_log("DB ERROR: " . $e->getMessage());
    die("Database connection failed");
}
