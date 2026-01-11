<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['profile_picture'];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Upload error occurred']);
        exit();
    }
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WebP files are allowed']);
        exit();
    }
    
    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
        exit();
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = 'uploads/profile_pictures/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $new_filename;
    
    // Delete old profile picture if exists
    $old_pic_query = "SELECT profile_picture FROM learners WHERE id = '$user_id'";
    $old_pic_result = mysqli_query($conn, $old_pic_query);
    if ($old_pic_result && mysqli_num_rows($old_pic_result) > 0) {
        $old_pic_row = mysqli_fetch_assoc($old_pic_result);
        if (!empty($old_pic_row['profile_picture']) && file_exists($old_pic_row['profile_picture'])) {
            unlink($old_pic_row['profile_picture']);
        }
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        // Update database
        $update_query = "UPDATE learners SET profile_picture = '$upload_path' WHERE id = '$user_id'";
        if (mysqli_query($conn, $update_query)) {
            echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully', 'image_path' => $upload_path]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database update failed']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}
?>