<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

// Allow access for debugging - you can remove this later
if (!isset($_SESSION['user_id'])) {
    // For debugging, continue without session check
    // echo json_encode(['success' => false, 'message' => 'User not logged in']);
    // exit();
}

try {
    // Query with creator information including profile picture
    $sql = "SELECT 
                c.id,
                c.creator_id,
                c.course_name,
                c.description,
                c.category,
                c.price,
                c.duration,
                c.students_enrolled,
                c.rating,
                c.total_reviews,
                c.status,
                c.created_at,
                c.updated_at,
                c.video_path,
                c.thumbnail,
                cr.full_name as creator_name,
                cr.profile_picture as creator_profile_picture
            FROM courses c 
            LEFT JOIN creators cr ON c.creator_id = cr.id 
            WHERE c.status = 'published'
            ORDER BY c.created_at DESC";
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        echo json_encode([
            'success' => false, 
            'message' => 'Database query failed: ' . mysqli_error($conn)
        ]);
        exit();
    }
    
    $courses = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $course = [
            'id' => intval($row['id']),
            'title' => $row['course_name'] ?: 'Untitled Course', // Map course_name to title
            'description' => $row['description'] ?: 'No description available',
            'category' => $row['category'] ?: 'General',
            'price' => floatval($row['price'] ?: 0),
            'duration' => intval($row['duration'] ?: 0),
            'students_enrolled' => intval($row['students_enrolled'] ?: 0),
            'rating' => floatval($row['rating'] ?: 0),
            'total_reviews' => intval($row['total_reviews'] ?: 0),
            'status' => $row['status'] ?: 'published',
            'creator_id' => intval($row['creator_id'] ?: 0),
            'created_at' => $row['created_at'] ?: date('Y-m-d H:i:s'),
            'updated_at' => $row['updated_at'] ?: date('Y-m-d H:i:s'),
            'video_path' => $row['video_path'] ?: '',
            'thumbnail' => $row['thumbnail'] ?: '',
            'instructor' => $row['creator_name'] ?: 'Course Instructor',
            'creator_name' => $row['creator_name'] ?: 'Unknown Creator',
            'creator_profile_picture' => $row['creator_profile_picture'] ?: ''
        ];
        $courses[] = $course;
    }
    
    echo json_encode([
        'success' => true,
        'courses' => $courses,
        'total_courses' => count($courses)
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>
