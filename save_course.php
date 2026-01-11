<?php
// Start output buffering to prevent any accidental output
ob_start();

// Suppress PHP errors and warnings from being output
error_reporting(0);
ini_set('display_errors', 0);

session_start();
include 'db.php';

// Set proper headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Clear any previous output
if (ob_get_length()) {
    ob_clean();
}

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    ob_end_flush();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    ob_end_flush();
    exit();
}

// Get form data
$courseName = mysqli_real_escape_string($conn, trim($_POST['courseName']));
$category = mysqli_real_escape_string($conn, trim($_POST['category']));
$description = mysqli_real_escape_string($conn, trim($_POST['description']));
$price = floatval($_POST['price']);
$duration = intval($_POST['duration']);
$creator_id = $_SESSION['user_id'];

// Debug logging
error_log("Course creation debug - Title: '$courseName', Category: '$category', Description: '$description', Price: $price, Duration: $duration");

// Validate inputs
$errors = [];

if (strlen($courseName) < 3) {
    $errors[] = 'Course name must be at least 3 characters';
}

if (empty($category)) {
    $errors[] = 'Category is required';
}

if (strlen($description) < 10) {
    $errors[] = 'Description must be at least 10 characters';
}

if ($price < 0) {
    $errors[] = 'Price cannot be negative';
}

if ($duration < 1 || $duration > 500) {
    $errors[] = 'Duration must be between 1 and 500 hours';
}

// Validate thumbnail upload
if (!isset($_FILES['courseThumbnail']) || $_FILES['courseThumbnail']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Thumbnail image is required';
} else {
    $thumbnail = $_FILES['courseThumbnail'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($thumbnail['type'], $allowedTypes)) {
        $errors[] = 'Thumbnail must be JPG, PNG, or WebP format';
    }
    
    if ($thumbnail['size'] > $maxSize) {
        $errors[] = 'Thumbnail size must be less than 5MB';
    }
}

// Validate video upload
if (!isset($_FILES['courseVideo']) || $_FILES['courseVideo']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Course video is required';
} else {
    $video = $_FILES['courseVideo'];
    $allowedVideoTypes = ['video/mp4', 'video/avi', 'video/mov', 'video/wmv'];
    $maxVideoSize = 100 * 1024 * 1024; // 100MB
    
    if (!in_array($video['type'], $allowedVideoTypes)) {
        $errors[] = 'Video must be MP4, AVI, MOV, or WMV format';
    }
    
    if ($video['size'] > $maxVideoSize) {
        $errors[] = 'Video size must be less than 100MB';
    }
}

// Validate NFT certificate upload
if (!isset($_FILES['nftCertificate']) || $_FILES['nftCertificate']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'NFT certificate template is required';
} else {
    $certificate = $_FILES['nftCertificate'];
    $allowedCertTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    $maxCertSize = 10 * 1024 * 1024; // 10MB
    
    if (!in_array($certificate['type'], $allowedCertTypes)) {
        $errors[] = 'Certificate must be JPG or PNG format';
    }
    
    if ($certificate['size'] > $maxCertSize) {
        $errors[] = 'Certificate size must be less than 10MB';
    }
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

// Check if courses table exists and what columns it has
$check_table = "SHOW TABLES LIKE 'courses'";
$table_result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Courses table does not exist. Please run the database schema first.']);
    exit();
}

// Check if thumbnail column exists, if not, add it
$check_columns = "SHOW COLUMNS FROM courses LIKE 'thumbnail'";
$thumbnail_col_result = mysqli_query($conn, $check_columns);

if (mysqli_num_rows($thumbnail_col_result) == 0) {
    // Add thumbnail column to courses table
    $add_thumbnail_col = "ALTER TABLE courses ADD COLUMN thumbnail VARCHAR(255) DEFAULT NULL";
    if (!mysqli_query($conn, $add_thumbnail_col)) {
        echo json_encode(['success' => false, 'message' => 'Failed to add thumbnail column to database']);
        exit();
    }
}

// Check if video_path column exists, if not, add it
$check_video_col = "SHOW COLUMNS FROM courses LIKE 'video_path'";
$video_col_result = mysqli_query($conn, $check_video_col);

if (mysqli_num_rows($video_col_result) == 0) {
    // Add video_path column to courses table
    $add_video_col = "ALTER TABLE courses ADD COLUMN video_path VARCHAR(500) DEFAULT NULL";
    if (!mysqli_query($conn, $add_video_col)) {
        echo json_encode(['success' => false, 'message' => 'Failed to add video_path column to database']);
        exit();
    }
}

// Check if nft_certificate_image column exists, if not, add it
$check_cert_col = "SHOW COLUMNS FROM courses LIKE 'nft_certificate_image'";
$cert_col_result = mysqli_query($conn, $check_cert_col);

if (mysqli_num_rows($cert_col_result) == 0) {
    // Add nft_certificate_image column to courses table
    $add_cert_col = "ALTER TABLE courses ADD COLUMN nft_certificate_image VARCHAR(255) DEFAULT NULL";
    if (!mysqli_query($conn, $add_cert_col)) {
        echo json_encode(['success' => false, 'message' => 'Failed to add nft_certificate_image column to database']);
        exit();
    }
}

// Handle thumbnail upload
$thumbnailPath = null;
if (isset($_FILES['courseThumbnail'])) {
    $thumbnail = $_FILES['courseThumbnail'];
    $uploadDir = 'uploads/course_thumbnails/';
    
    // Create unique filename
    $fileExtension = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
    $uniqueFilename = 'course_' . $creator_id . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $thumbnailPath = $uploadDir . $uniqueFilename;
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload thumbnail']);
        exit();
    }
}

// Handle video upload
$videoPath = null;
if (isset($_FILES['courseVideo'])) {
    $video = $_FILES['courseVideo'];
    $videoUploadDir = 'uploads/course_videos/';
    
    // Create unique filename for video
    $videoFileExtension = pathinfo($video['name'], PATHINFO_EXTENSION);
    $uniqueVideoFilename = 'course_' . $creator_id . '_' . time() . '_' . uniqid() . '.' . $videoFileExtension;
    $videoPath = $videoUploadDir . $uniqueVideoFilename;
    
    // Create directory if it doesn't exist
    if (!file_exists($videoUploadDir)) {
        mkdir($videoUploadDir, 0777, true);
    }
    
    // Move uploaded video file
    if (!move_uploaded_file($video['tmp_name'], $videoPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload video']);
        exit();
    }
}

// Handle NFT certificate upload
$certificatePath = null;
if (isset($_FILES['nftCertificate'])) {
    $certificate = $_FILES['nftCertificate'];
    $certUploadDir = 'uploads/nft_certificates/';
    
    // Create unique filename for certificate
    $certFileExtension = pathinfo($certificate['name'], PATHINFO_EXTENSION);
    $uniqueCertFilename = 'cert_' . $creator_id . '_' . time() . '_' . uniqid() . '.' . $certFileExtension;
    $certificatePath = $certUploadDir . $uniqueCertFilename;
    
    // Create directory if it doesn't exist
    if (!file_exists($certUploadDir)) {
        mkdir($certUploadDir, 0777, true);
    }
    
    // Move uploaded certificate file
    if (!move_uploaded_file($certificate['tmp_name'], $certificatePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload NFT certificate']);
        exit();
    }
}

// Insert course into database - using dynamic column detection
$check_columns = "SHOW COLUMNS FROM courses";
$columns_result = mysqli_query($conn, $check_columns);
$columns = [];
while ($col = mysqli_fetch_assoc($columns_result)) {
    $columns[] = $col['Field'];
}

// Determine which columns to use based on what exists
$title_col = in_array('course_name', $columns) ? 'course_name' : 'title';
$duration_col = in_array('duration', $columns) ? 'duration' : 'duration_hours';

// Check if nft_certificate_image column exists and include certificate path
if (in_array('nft_certificate_image', $columns) && $certificatePath) {
    $sql = "INSERT INTO courses (creator_id, $title_col, description, category, price, $duration_col, thumbnail, video_path, nft_certificate_image) 
            VALUES ('$creator_id', '$courseName', '$description', '$category', '$price', '$duration', '$thumbnailPath', '$videoPath', '$certificatePath')";
} else {
    $sql = "INSERT INTO courses (creator_id, $title_col, description, category, price, $duration_col, thumbnail, video_path) 
            VALUES ('$creator_id', '$courseName', '$description', '$category', '$price', '$duration', '$thumbnailPath', '$videoPath')";
}

if (mysqli_query($conn, $sql)) {
    $course_id = mysqli_insert_id($conn);
    
    // Update creator's total courses count if creators table has total_courses column
    $check_creator_cols = "SHOW COLUMNS FROM creators LIKE 'total_courses'";
    $creator_col_result = mysqli_query($conn, $check_creator_cols);
    
    if (mysqli_num_rows($creator_col_result) > 0) {
        $update_creator = "UPDATE creators SET total_courses = (SELECT COUNT(*) FROM courses WHERE creator_id = '$creator_id') WHERE id = '$creator_id'";
        mysqli_query($conn, $update_creator);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Course with thumbnail and video created successfully',
        'course_id' => $course_id,
        'thumbnail_path' => $thumbnailPath,
        'video_path' => $videoPath
    ]);
} else {
    // If database insert fails, clean up uploaded files
    if ($thumbnailPath && file_exists($thumbnailPath)) {
        unlink($thumbnailPath);
    }
    if ($videoPath && file_exists($videoPath)) {
        unlink($videoPath);
    }
    
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}
?>

// Get form data
$courseName = mysqli_real_escape_string($conn, trim($_POST['courseName']));
$category = mysqli_real_escape_string($conn, trim($_POST['category']));
$description = mysqli_real_escape_string($conn, trim($_POST['description']));
$price = floatval($_POST['price']);
$duration = intval($_POST['duration']);
$creator_id = $_SESSION['user_id'];

// Debug logging
error_log("Course creation debug - Title: '$courseName', Category: '$category', Description: '$description', Price: $price, Duration: $duration");

// Validate inputs
$errors = [];

if (strlen($courseName) < 3) {
    $errors[] = 'Course name must be at least 3 characters';
}

if (empty($category)) {
    $errors[] = 'Category is required';
}

if (strlen($description) < 10) {
    $errors[] = 'Description must be at least 10 characters';
}

if ($price < 0) {
    $errors[] = 'Price cannot be negative';
}

if ($duration < 1 || $duration > 500) {
    $errors[] = 'Duration must be between 1 and 500 hours';
}

// Validate thumbnail upload
if (!isset($_FILES['courseThumbnail']) || $_FILES['courseThumbnail']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Thumbnail image is required';
} else {
    $thumbnail = $_FILES['courseThumbnail'];
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($thumbnail['type'], $allowedTypes)) {
        $errors[] = 'Thumbnail must be JPG, PNG, or WebP format';
    }
    
    if ($thumbnail['size'] > $maxSize) {
        $errors[] = 'Thumbnail size must be less than 5MB';
    }
}

// Validate video upload (keeping existing validation)
if (!isset($_FILES['courseVideo']) || $_FILES['courseVideo']['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Course video is required';
}

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit();
}

// Check if courses table exists and what columns it has
$check_table = "SHOW TABLES LIKE 'courses'";
$table_result = mysqli_query($conn, $check_table);

if (mysqli_num_rows($table_result) == 0) {
    echo json_encode(['success' => false, 'message' => 'Courses table does not exist. Please run the database schema first.']);
    exit();
}

// Check if thumbnail column exists, if not, add it
$check_columns = "SHOW COLUMNS FROM courses LIKE 'thumbnail'";
$thumbnail_col_result = mysqli_query($conn, $check_columns);

if (mysqli_num_rows($thumbnail_col_result) == 0) {
    // Add thumbnail column to courses table
    $add_thumbnail_col = "ALTER TABLE courses ADD COLUMN thumbnail VARCHAR(255) DEFAULT NULL";
    if (!mysqli_query($conn, $add_thumbnail_col)) {
        echo json_encode(['success' => false, 'message' => 'Failed to add thumbnail column to database']);
        exit();
    }
}

// Handle thumbnail upload
$thumbnailPath = null;
if (isset($_FILES['courseThumbnail'])) {
    $thumbnail = $_FILES['courseThumbnail'];
    $uploadDir = 'uploads/course_thumbnails/';
    
    // Create unique filename
    $fileExtension = pathinfo($thumbnail['name'], PATHINFO_EXTENSION);
    $uniqueFilename = 'course_' . $creator_id . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $thumbnailPath = $uploadDir . $uniqueFilename;
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($thumbnail['tmp_name'], $thumbnailPath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload thumbnail']);
        exit();
    }
}

// Handle NFT certificate upload for thumbnail-only path
$certificatePath = null;
if (isset($_FILES['nftCertificate'])) {
    $certificate = $_FILES['nftCertificate'];
    $certUploadDir = 'uploads/nft_certificates/';
    
    // Create unique filename for certificate
    $certFileExtension = pathinfo($certificate['name'], PATHINFO_EXTENSION);
    $uniqueCertFilename = 'cert_' . $creator_id . '_' . time() . '_' . uniqid() . '.' . $certFileExtension;
    $certificatePath = $certUploadDir . $uniqueCertFilename;
    
    // Create directory if it doesn't exist
    if (!file_exists($certUploadDir)) {
        mkdir($certUploadDir, 0777, true);
    }
    
    // Move uploaded certificate file
    if (!move_uploaded_file($certificate['tmp_name'], $certificatePath)) {
        echo json_encode(['success' => false, 'message' => 'Failed to upload NFT certificate']);
        exit();
    }
}

// Insert course into database - using dynamic column detection
$check_columns = "SHOW COLUMNS FROM courses";
$columns_result = mysqli_query($conn, $check_columns);
$columns = [];
while ($col = mysqli_fetch_assoc($columns_result)) {
    $columns[] = $col['Field'];
}

// Determine which columns to use based on what exists
$title_col = in_array('course_name', $columns) ? 'course_name' : 'title';
$duration_col = in_array('duration', $columns) ? 'duration' : 'duration_hours';

// Check if nft_certificate_image column exists and include certificate path
if (in_array('nft_certificate_image', $columns) && $certificatePath) {
    $sql = "INSERT INTO courses (creator_id, $title_col, description, category, price, $duration_col, thumbnail, video_path, nft_certificate_image) 
            VALUES ('$creator_id', '$courseName', '$description', '$category', '$price', '$duration', '$thumbnailPath', '$videoPath', '$certificatePath')";
} else {
    $sql = "INSERT INTO courses (creator_id, $title_col, description, category, price, $duration_col, thumbnail, video_path) 
            VALUES ('$creator_id', '$courseName', '$description', '$category', '$price', '$duration', '$thumbnailPath', '$videoPath')";
}

if (mysqli_query($conn, $sql)) {
    $course_id = mysqli_insert_id($conn);
    
    // Update creator's total courses count if creators table has total_courses column
    $check_creator_cols = "SHOW COLUMNS FROM creators LIKE 'total_courses'";
    $creator_col_result = mysqli_query($conn, $check_creator_cols);
    
    if (mysqli_num_rows($creator_col_result) > 0) {
        $update_creator = "UPDATE creators SET total_courses = (SELECT COUNT(*) FROM courses WHERE creator_id = '$creator_id') WHERE id = '$creator_id'";
        mysqli_query($conn, $update_creator);
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Course with thumbnail and video created successfully',
        'course_id' => $course_id,
        'thumbnail_path' => $thumbnailPath,
        'video_path' => $videoPath
    ]);
} else {
    // If database insert fails, clean up uploaded files
    if ($thumbnailPath && file_exists($thumbnailPath)) {
        unlink($thumbnailPath);
    }
    if ($videoPath && file_exists($videoPath)) {
        unlink($videoPath);
    }
    // Clean output buffer and send JSON response
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . mysqli_error($conn)]);
}

// Clean output buffer before ending
ob_end_clean();
?>