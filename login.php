<?php

// login.php - checks admins, learners, creators tables and returns JSON
session_start();
require_once __DIR__ . '/db.php';

header('Content-Type: application/json; charset=utf-8');

// Validate HTTP method - only POST requests are allowed for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Sanitize and extract POST data
// trim() removes whitespace from email and role to prevent formatting issues
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : ''; // Don't trim password (may contain intentional spaces)
$role = isset($_POST['role']) ? trim($_POST['role']) : '';

// VALIDATION 1: Email validation
// Check if email exists and has valid format using PHP's built-in filter
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email required.']); exit;
}

// VALIDATION 2: Password validation
// Ensure password pfield is not empty (length/complexity checks can be added here)
if (!$password) {
    echo json_encode(['success' => false, 'message' => 'Password required.']); exit;
}

// VALIDATION 3: Role validation
// Ensure role is one of the allowed values using strict comparison (prevents type juggling attacks)
if (!in_array($role, ['admin','learner','creator'], true)) {
    echo json_encode(['success' => false, 'message' => 'Invalid role.']); exit;
}

// Map user roles to their corresponding database tables
$tables = [
    'admin' => 'admins',
    'learner' => 'learners',
    'creator' => 'creators'
];

// VALIDATION 4: Table mapping validation
// Ensure the role maps to a valid database table
$table = $tables[$role] ?? null;
if (!$table) {
    echo json_encode(['success' => false, 'message' => 'Role not supported.']); exit;
}

// DATABASE QUERY: Fetch user by email using prepared statement
// Using prepared statements prevents SQL injection attacks
// columns: id, email, password, full_name (if exist)
$sql = "SELECT id, email, password, full_name FROM `$table` WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

// VALIDATION 5: Database preparation validation
// Check if SQL statement preparation was successful
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Database error.']); exit;
}

// Bind email parameter and execute query
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// VALIDATION 6: User existence validation
// Check if user exists in the database
if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found.']); exit;
}

// VALIDATION 7: Password verification
// Use password_verify() to check hashed password securely
// Assumes passwords are stored using password_hash() function
if (!password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Incorrect password.']); exit;
}

// login success: set session and return redirect
$_SESSION['user_id'] = $user['id'];
$_SESSION['email'] = $user['email'];
$_SESSION['role'] = $role;
$_SESSION['full_name'] = $user['full_name'] ?? '';

$redirect = '/';
if ($role === 'admin') $redirect = 'admin.php';
elseif ($role === 'learner') $redirect = 'home-learner.php';
elseif ($role === 'creator') $redirect = 'home-creator.php';

echo json_encode(['success' => true, 'redirect' => $redirect]);
exit;
?>