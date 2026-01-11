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

// Extract and validate data
$course_id = intval($input['courseId'] ?? 0);
$course_name = trim($input['courseName'] ?? '');
$category = trim($input['category'] ?? '');
$description = trim($input['description'] ?? '');
$price = floatval($input['price'] ?? 0);
$duration = intval($input['duration'] ?? 0);
$creator_id = $_SESSION['user_id'];

// Validation
if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

if (strlen($course_name) < 3) {
    echo json_encode(['success' => false, 'message' => 'Course name must be at least 3 characters']);
    exit();
}

if (empty($category)) {
    echo json_encode(['success' => false, 'message' => 'Please select a category']);
    exit();
}

if (strlen($description) < 10) {
    echo json_encode(['success' => false, 'message' => 'Description must be at least 10 characters']);
    exit();
}

if ($price < 0) {
    echo json_encode(['success' => false, 'message' => 'Price must be 0 or greater']);
    exit();
}

if ($duration < 1 || $duration > 500) {
    echo json_encode(['success' => false, 'message' => 'Duration must be between 1 and 500 hours']);
    exit();
}

try {
    // First check what columns exist in the courses table
    $columns_query = "SHOW COLUMNS FROM courses";
    $columns_result = $conn->query($columns_query);
    $available_columns = [];
    
    while ($column = $columns_result->fetch_assoc()) {
        $available_columns[] = $column['Field'];
    }
    
    // Build update query based on available columns
    $update_fields = [];
    $update_values = [];
    $update_types = '';
    
    // Check for course name column (could be 'course_name' or 'title')
    if (in_array('course_name', $available_columns)) {
        $update_fields[] = 'course_name = ?';
        $update_values[] = $course_name;
        $update_types .= 's';
    } elseif (in_array('title', $available_columns)) {
        $update_fields[] = 'title = ?';
        $update_values[] = $course_name;
        $update_types .= 's';
    }
    
    // Add other fields if they exist
    if (in_array('category', $available_columns)) {
        $update_fields[] = 'category = ?';
        $update_values[] = $category;
        $update_types .= 's';
    }
    
    if (in_array('description', $available_columns)) {
        $update_fields[] = 'description = ?';
        $update_values[] = $description;
        $update_types .= 's';
    }
    
    if (in_array('price', $available_columns)) {
        $update_fields[] = 'price = ?';
        $update_values[] = $price;
        $update_types .= 'd';
    }
    
    if (in_array('duration', $available_columns)) {
        $update_fields[] = 'duration = ?';
        $update_values[] = $duration;
        $update_types .= 'i';
    }
    
    // Add updated_at if it exists
    if (in_array('updated_at', $available_columns)) {
        $update_fields[] = 'updated_at = NOW()';
    }
    
    if (empty($update_fields)) {
        echo json_encode(['success' => false, 'message' => 'No valid fields to update']);
        exit();
    }
    
    // Add WHERE clause parameters
    $update_values[] = $course_id;
    $update_values[] = $creator_id;
    $update_types .= 'ii';
    
    $update_sql = "UPDATE courses SET " . implode(', ', $update_fields) . " WHERE id = ? AND creator_id = ?";
    
    // Verify course exists and belongs to this creator
    $check_stmt = $conn->prepare("SELECT id FROM courses WHERE id = ? AND creator_id = ?");
    $check_stmt->bind_param("ii", $course_id, $creator_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
        exit();
    }
    $check_stmt->close();
    
    // Update the course
    $update_stmt = $conn->prepare($update_sql);
    if (!$update_stmt) {
        echo json_encode(['success' => false, 'message' => 'Database prepare error: ' . $conn->error]);
        exit();
    }
    
    $update_stmt->bind_param($update_types, ...$update_values);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        echo json_encode([
            'success' => true, 
            'message' => 'Course updated successfully',
            'debug' => [
                'available_columns' => $available_columns,
                'updated_fields' => $update_fields,
                'sql' => $update_sql
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update course: ' . $conn->error]);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>