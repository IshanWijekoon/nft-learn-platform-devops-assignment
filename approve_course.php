<?php
session_start();
include 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['course_id']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$course_id = intval($input['course_id']);
$action = $input['action'];

// Validate course exists
$course_check = "SELECT id, status, creator_id FROM courses WHERE id = $course_id";
$course_result = mysqli_query($conn, $course_check);

if (!$course_result || mysqli_num_rows($course_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    exit();
}

$course = mysqli_fetch_assoc($course_result);

switch ($action) {
    case 'approve':
        $update_query = "UPDATE courses SET status = 'published', approved_at = NOW() WHERE id = $course_id";
        $success_message = "Course approved successfully";
        break;
        
    case 'reject':
        $reason = isset($input['reason']) ? mysqli_real_escape_string($conn, $input['reason']) : 'No reason provided';
        $update_query = "UPDATE courses SET status = 'rejected', rejection_reason = '$reason', rejected_at = NOW() WHERE id = $course_id";
        $success_message = "Course rejected successfully";
        break;
        
    case 'suspend':
        $update_query = "UPDATE courses SET status = 'suspended', suspended_at = NOW() WHERE id = $course_id";
        $success_message = "Course suspended successfully";
        break;
        
    case 'delete':
        // First check if there are enrollments
        $enrollment_check = "SELECT COUNT(*) as count FROM enrollments WHERE course_id = $course_id";
        $enrollment_result = mysqli_query($conn, $enrollment_check);
        $enrollment_count = mysqli_fetch_assoc($enrollment_result)['count'];
        
        if ($enrollment_count > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "Cannot delete course: $enrollment_count learner(s) are enrolled. Please contact learners to withdraw first."
            ]);
            exit();
        }
        
        // Get course details for logging
        $course_detail_query = "SELECT course_name, thumbnail FROM courses WHERE id = $course_id";
        $course_detail_result = mysqli_query($conn, $course_detail_query);
        $course_detail = mysqli_fetch_assoc($course_detail_result);
        
        if (!$course_detail) {
            echo json_encode(['success' => false, 'message' => 'Course not found']);
            exit();
        }
        
        $course_name = $course_detail['course_name'];
        $files_deleted = 0;
        $deletion_errors = [];
        
        // Delete course thumbnail if it exists
        if ($course_detail['thumbnail'] && file_exists($course_detail['thumbnail'])) {
            if (unlink($course_detail['thumbnail'])) {
                $files_deleted++;
            } else {
                $deletion_errors[] = "Failed to delete thumbnail: " . $course_detail['thumbnail'];
            }
        }
        
        // Delete course videos and their records
        $video_query = "SELECT video_path FROM course_videos WHERE course_id = $course_id";
        $video_result = mysqli_query($conn, $video_query);
        $video_count = 0;
        
        while ($video = mysqli_fetch_assoc($video_result)) {
            $video_count++;
            if (file_exists($video['video_path'])) {
                if (unlink($video['video_path'])) {
                    $files_deleted++;
                } else {
                    $deletion_errors[] = "Failed to delete video: " . $video['video_path'];
                }
            }
        }
        
        // Delete course videos records from database
        $video_delete_result = mysqli_query($conn, "DELETE FROM course_videos WHERE course_id = $course_id");
        if (!$video_delete_result) {
            $deletion_errors[] = "Failed to delete video records from database";
        }
        
        // Delete the course
        $update_query = "DELETE FROM courses WHERE id = $course_id";
        $success_message = "Course '$course_name' deleted successfully. $files_deleted file(s) removed.";
        
        if (!empty($deletion_errors)) {
            $success_message .= " Warning: " . implode("; ", $deletion_errors);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        exit();
}

// Execute the update
if (mysqli_query($conn, $update_query)) {
    // Log the admin action
    $admin_id = $_SESSION['user_id'];
    $log_action = ucfirst($action);
    $log_query = "INSERT INTO admin_actions (admin_id, action_type, target_type, target_id, details, created_at) 
                  VALUES ($admin_id, '$log_action', 'course', $course_id, 'Course $action by admin', NOW())";
    mysqli_query($conn, $log_query);
    
    echo json_encode(['success' => true, 'message' => $success_message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>