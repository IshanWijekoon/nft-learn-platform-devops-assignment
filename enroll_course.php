<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$course_id = intval($input['course_id'] ?? $input['courseId'] ?? 0);
$learner_id = $_SESSION['user_id'];

// Debug logging
error_log("Enrollment attempt - Learner ID: $learner_id, Course ID: $course_id");

if (!$course_id) {
    error_log("Invalid course ID provided");
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

try {
    // Check if course exists
    $course_check = "SELECT id, course_name, creator_id, students_enrolled FROM courses WHERE id = ? AND status = 'published'";
    $stmt = mysqli_prepare($conn, $course_check);
    mysqli_stmt_bind_param($stmt, 'i', $course_id);
    mysqli_stmt_execute($stmt);
    $course_result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($course_result) === 0) {
        echo json_encode(['success' => false, 'message' => 'Course not found or not available']);
        exit();
    }
    
    $course = mysqli_fetch_assoc($course_result);
    
    // Check if learner is trying to enroll in their own course (if they're also a creator)
    if ($_SESSION['role'] === 'creator' && $course['creator_id'] == $learner_id) {
        echo json_encode(['success' => false, 'message' => 'You cannot enroll in your own course']);
        exit();
    }
    
    // Update the enrollments table structure to match your schema
    $create_table = "CREATE TABLE IF NOT EXISTS enrollments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        learner_id INT NOT NULL,
        course_id INT NOT NULL,
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        progress DECIMAL(5,2) DEFAULT 0.00,
        completed BOOLEAN DEFAULT FALSE,
        completed_at TIMESTAMP NULL,
        UNIQUE KEY unique_enrollment (learner_id, course_id),
        INDEX idx_learner (learner_id),
        INDEX idx_course (course_id),
        FOREIGN KEY (learner_id) REFERENCES learners(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )";
    mysqli_query($conn, $create_table);
    
    // Check if already enrolled using prepared statement
    $enrollment_check = "SELECT id FROM enrollments WHERE learner_id = ? AND course_id = ?";
    $check_stmt = mysqli_prepare($conn, $enrollment_check);
    mysqli_stmt_bind_param($check_stmt, 'ii', $learner_id, $course_id);
    mysqli_stmt_execute($check_stmt);
    $enrollment_result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($enrollment_result) > 0) {
        echo json_encode(['success' => false, 'message' => 'You are already enrolled in this course']);
        exit();
    }
    
    // Start transaction
    mysqli_autocommit($conn, FALSE);
    
    // Insert enrollment using prepared statement
    $enroll_sql = "INSERT INTO enrollments (learner_id, course_id, enrolled_at, progress, completed) VALUES (?, ?, NOW(), 0.00, FALSE)";
    $enroll_stmt = mysqli_prepare($conn, $enroll_sql);
    mysqli_stmt_bind_param($enroll_stmt, 'ii', $learner_id, $course_id);
    $enroll_success = mysqli_stmt_execute($enroll_stmt);
    
    if (!$enroll_success) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => 'Failed to enroll: ' . mysqli_error($conn)]);
        exit();
    }
    
    // Update course enrollment count using prepared statement
    $new_count = intval($course['students_enrolled']) + 1;
    $update_course = "UPDATE courses SET students_enrolled = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_course);
    mysqli_stmt_bind_param($update_stmt, 'ii', $new_count, $course_id);
    $update_success = mysqli_stmt_execute($update_stmt);
    
    if (!$update_success) {
        mysqli_rollback($conn);
        echo json_encode(['success' => false, 'message' => 'Failed to update course statistics']);
        exit();
    }
    
    // Update creator's total students if creators table has total_students column
    $creator_id = $course['creator_id'];
    $check_creator_cols = "SHOW COLUMNS FROM creators LIKE 'total_students'";
    $creator_col_result = mysqli_query($conn, $check_creator_cols);
    
    if (mysqli_num_rows($creator_col_result) > 0) {
        $update_creator = "UPDATE creators SET 
                          total_students = (
                              SELECT COALESCE(SUM(students_enrolled), 0) 
                              FROM courses 
                              WHERE creator_id = '$creator_id'
                          ) 
                          WHERE id = '$creator_id'";
        mysqli_query($conn, $update_creator);
    }
    
    // Commit transaction
    mysqli_commit($conn);
    mysqli_autocommit($conn, TRUE);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Successfully enrolled in "' . $course['course_name'] . '"!',
        'course_id' => $course_id,
        'enrollment_date' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($conn);
    mysqli_autocommit($conn, TRUE);
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
