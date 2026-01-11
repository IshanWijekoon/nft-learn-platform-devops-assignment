<?php
// Disable error output to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// Include database connection
try {
    include 'db.php';
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$raw_input = file_get_contents('php://input');
$input = json_decode($raw_input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$course_id = intval($input['courseId'] ?? 0);
$creator_id = $_SESSION['user_id'];

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

try {
    // Get course details with ownership verification
    $stmt = $conn->prepare("SELECT id, course_name, category, description, price, duration FROM courses WHERE id = ? AND creator_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit();
    }
    
    $stmt->bind_param("ii", $course_id, $creator_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
        exit();
    }

    $course = $result->fetch_assoc();
    $stmt->close();

    // Map course_name to title for frontend compatibility
    $course['title'] = $course['course_name'] ?? 'Untitled Course';

    echo json_encode([
        'success' => true, 
        'course' => $course
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
