<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    echo json_encode(['success' => false, 'message' => 'Not authorized - User ID: ' . ($_SESSION['user_id'] ?? 'none') . ', Role: ' . ($_SESSION['role'] ?? 'none')]);
    exit();
}

$creator_id = $_SESSION['user_id'];

// First check if courses table exists and what columns it has
$check_table = "SHOW TABLES LIKE 'courses'";
$table_result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_result) == 0) {
    // Create courses table if it doesn't exist
    $create_table = "CREATE TABLE courses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        course_name VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(100),
        price DECIMAL(10,2) DEFAULT 0,
        duration INT DEFAULT 0,
        creator_id INT NOT NULL,
        video_path VARCHAR(500),
        thumbnail VARCHAR(500),
        nft_certificate_path VARCHAR(500),
        students_enrolled INT DEFAULT 0,
        rating DECIMAL(3,2) DEFAULT 0,
        total_reviews INT DEFAULT 0,
        status VARCHAR(50) DEFAULT 'published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if (mysqli_query($conn, $create_table)) {
        // Add sample course
        $sample = "INSERT INTO courses (course_name, description, category, price, duration, creator_id) 
                   VALUES ('Sample Course', 'This is a sample course for testing', 'Programming', 29.99, 5, $creator_id)";
        mysqli_query($conn, $sample);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create courses table: ' . mysqli_error($conn)]);
        exit();
    }
}

// Table exists, proceed with course retrieval

// Get courses for this creator with simple query
$sql = "SELECT * FROM courses WHERE creator_id = '$creator_id' ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if ($result) {
    $courses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $course = [
            'id' => $row['id'],
            'title' => $row['course_name'] ?? 'Untitled Course',
            'description' => $row['description'] ?? 'No description available',
            'category' => $row['category'] ?? 'General',
            'price' => $row['price'] ?? 0,
            'duration' => $row['duration'] ?? 0,
            'students_enrolled' => $row['students_enrolled'] ?? 0,
            'rating' => $row['rating'] ?? 0,
            'total_reviews' => $row['total_reviews'] ?? 0,
            'status' => $row['status'] ?? 'published',
            'video_path' => $row['video_path'] ?? '',
            'thumbnail' => $row['thumbnail'] ?? '',
            'created_at' => $row['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $row['updated_at'] ?? date('Y-m-d H:i:s')
        ];
        $courses[] = $course;
    }
    
    echo json_encode([
        'success' => true, 
        'courses' => $courses,
        'debug' => [
            'total_courses' => count($courses),
            'creator_id' => $creator_id,
            'sql_query' => $sql
        ]
    ]);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . mysqli_error($conn),
        'sql' => $sql
    ]);
}
?>