<?php
session_start();
include 'db.php';

function bad($msg) {
    // simple error response; you can redirect with query string in real app
    echo '<script>alert(' . json_encode($msg) . '); window.history.back();</script>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    bad('Invalid request method.');
}

$role = isset($_POST['role']) ? trim($_POST['role']) : '';
$fullName = isset($_POST['fullName']) ? trim($_POST['fullName']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$idNumber = isset($_POST['idNumber']) ? trim($_POST['idNumber']) : '';
$walletAddress = isset($_POST['walletAddress']) ? trim($_POST['walletAddress']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirmPassword = isset($_POST['confirmPassword']) ? $_POST['confirmPassword'] : '';

// Basic server-side validation
if (!$fullName || strlen($fullName) < 2) bad('Full name required.');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) bad('Valid email required.');
if (!$idNumber || strlen($idNumber) < 5) bad('Valid ID number required.');
if (!$password || strlen($password) < 8) bad('Password must be 8+ chars.');
if ($password !== $confirmPassword) bad('Passwords do not match.');

if ($role !== 'learner' && $role !== 'creator') bad('Invalid role.');

if ($role === 'learner' && (!$walletAddress || strlen($walletAddress) < 10)) {
    bad('Wallet address required for learners.');
}

// choose table
$table = $role === 'learner' ? 'learners' : 'creators';

// check if email already exists in target table
$stmt = $conn->prepare("SELECT id FROM `$table` WHERE email = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    bad('Email already registered.');
}
$stmt->close();

// insert
$hashed = password_hash($password, PASSWORD_DEFAULT);

if ($role === 'learner') {
    $stmt = $conn->prepare("INSERT INTO learners (full_name, email, id_number, wallet_address, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param('sssss', $fullName, $email, $idNumber, $walletAddress, $hashed);
} else {
    // creators table (no wallet required)
    $stmt = $conn->prepare("INSERT INTO creators (full_name, email, id_number, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('ssss', $fullName, $email, $idNumber, $hashed);
}

if ($stmt->execute()) {
    $stmt->close();
    // success: redirect to login page
    header('Location: login.html?registered=1');
    exit;
} else {
    $stmt->close();
    bad('Registration failed. Try again.');
}
?>