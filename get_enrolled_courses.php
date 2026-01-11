<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

$learner_id = $_SESSION['user_id'];

try {
    // First, check if enrollments table exists
    $check_table = "SHOW TABLES LIKE 'enrollments'";
    $table_result = mysqli_query($conn, $check_table);
    
    if (mysqli_num_rows($table_result) == 0) {
        // Create enrollments table if it doesn't exist
        $create_table = "CREATE TABLE enrollments (
            id INT PRIMARY KEY AUTO_INCREMENT,
            learner_id INT NOT NULL,
            course_id INT NOT NULL,
            enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            progress DECIMAL(5,2) DEFAULT 0.00,
            completed BOOLEAN DEFAULT FALSE,
            UNIQUE KEY unique_enrollment (learner_id, course_id),
            INDEX idx_learner (learner_id),
            INDEX idx_course (course_id)
        )";
        mysqli_query($conn, $create_table);
    }
    
    // Get enrolled courses for this learner
    $sql = "SELECT 
                e.course_id,
                e.enrolled_at,
                e.progress,
                e.completed,
                c.title,
                c.description,
                c.category,
                c.price,
                c.duration,
                cr.full_name as creator_name
            FROM enrollments e
            LEFT JOIN courses c ON e.course_id = c.id
            LEFT JOIN creators cr ON c.creator_id = cr.id
            WHERE e.learner_id = '$learner_id'
            ORDER BY e.enrolled_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if ($result) {
        $enrolled_courses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $enrolled_courses[] = $row;
        }
        
        echo json_encode([
            'success' => true, 
            'enrolled_courses' => $enrolled_courses,
            'total_enrolled' => count($enrolled_courses)
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . mysqli_error($conn)
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
