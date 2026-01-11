<?php
// Disable error output to prevent HTML in JSON response
error_reporting(0);
ini_set('display_errors', 0);

session_start();

// Set JSON header first
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
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input', 'debug' => $raw_input]);
    exit();
}

$course_id = intval($input['courseId'] ?? 0);
$creator_id = $_SESSION['user_id'];

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID', 'received' => $input]);
    exit();
}

// Try to get course details and verify ownership
try {
    // First, let's check what columns actually exist in the courses table
    $columns_query = "SHOW COLUMNS FROM courses";
    $columns_result = $conn->query($columns_query);
    $available_columns = [];
    
    while ($column = $columns_result->fetch_assoc()) {
        $available_columns[] = $column['Field'];
    }
    
    // Build the SELECT query with only existing columns
    $select_fields = ['id'];
    $possible_file_columns = ['video_path', 'thumbnail', 'nft_certificate_template', 'nft_certificate', 'certificate_template'];
    
    foreach ($possible_file_columns as $col) {
        if (in_array($col, $available_columns)) {
            $select_fields[] = $col;
        }
    }
    
    $select_sql = "SELECT " . implode(', ', $select_fields) . " FROM courses WHERE id = ? AND creator_id = ?";
    
    // Check if course exists and belongs to this creator
    $stmt = $conn->prepare($select_sql);
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

    // Simple deletion approach - just delete the course record
    $delete_stmt = $conn->prepare("DELETE FROM courses WHERE id = ? AND creator_id = ?");
    if (!$delete_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit();
    }
    
    $delete_stmt->bind_param("ii", $course_id, $creator_id);
    
    if ($delete_stmt->execute()) {
        $delete_stmt->close();
        
        // Clean up files if they exist - check all possible file column names
        $file_paths = [];
        foreach ($possible_file_columns as $col) {
            if (isset($course[$col]) && !empty($course[$col])) {
                $file_paths[] = $course[$col];
            }
        }
        
        // Delete files
        foreach ($file_paths as $file_path) {
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Course deleted successfully', 'debug' => [
            'available_columns' => $available_columns,
            'deleted_files' => $file_paths
        ]]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete course: ' . $conn->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>