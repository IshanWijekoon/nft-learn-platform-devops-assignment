<?php
session_start();
include 'db.php';
include 'nft_certificate_system.php';

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

// Get course ID from POST data
$course_id = intval($_POST['course_id']);
$learner_id = $_SESSION['user_id'];

// Validate input
if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

// Check if enrollment exists
$check_enrollment = "SELECT * FROM enrollments WHERE learner_id = '$learner_id' AND course_id = '$course_id'";
$enrollment_result = mysqli_query($conn, $check_enrollment);

if (!$enrollment_result || mysqli_num_rows($enrollment_result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Enrollment not found']);
    exit();
}

$enrollment = mysqli_fetch_assoc($enrollment_result);

// Check if already completed
if ($enrollment['completed']) {
    echo json_encode(['success' => true, 'message' => 'Course already completed']);
    exit();
}

// Mark course as completed
$update_query = "UPDATE enrollments 
                SET completed = 1, 
                    completed_at = NOW(), 
                    progress = 100.00,
                    completion_date = NOW()
                WHERE learner_id = '$learner_id' AND course_id = '$course_id'";

if (mysqli_query($conn, $update_query)) {
    // Award NFT Certificate automatically upon course completion
    error_log("Attempting to award NFT certificate for course_id: $course_id, learner_id: $learner_id");
    $certificate_result = awardNFTCertificate($course_id, $learner_id);
    error_log("Certificate result: " . json_encode($certificate_result));
    
    $response = [
        'success' => true, 
        'message' => 'Course completed successfully!'
    ];
    
    if ($certificate_result && $certificate_result['success']) {
        $response['certificate_awarded'] = true;
        $response['nft_key'] = $certificate_result['nft_key'];
        $response['verification_code'] = $certificate_result['verification_code'];
        $response['certificate_message'] = 'Congratulations! You have been awarded an NFT certificate!';
        $response['certificate_url'] = $certificate_result['verification_url'] ?? '';
    } else {
        $response['certificate_awarded'] = false;
        $response['certificate_error'] = $certificate_result['message'] ?? 'Unknown certificate error';
        error_log("Certificate award failed: " . ($certificate_result['message'] ?? 'No error message'));
    }
    
    echo json_encode($response);
} else {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . mysqli_error($conn)
    ]);
}
?>