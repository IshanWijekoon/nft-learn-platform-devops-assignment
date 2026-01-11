<?php
/**
 * Award NFT Certificate Endpoint
 * Called when a learner completes a course to award them an NFT certificate
 */

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

// Get input data
$input = json_decode(file_get_contents('php://input'), true);
$course_id = intval($input['course_id']);
$learner_id = $_SESSION['user_id'];

if (!$course_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid course ID']);
    exit();
}

// Verify the learner is enrolled in this course
$enrollment_stmt = $conn->prepare("SELECT id FROM enrollments WHERE course_id = ? AND learner_id = ?");
$enrollment_stmt->bind_param("ii", $course_id, $learner_id);
$enrollment_stmt->execute();
$enrollment_result = $enrollment_stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You are not enrolled in this course']);
    exit();
}

// Check if course has been completed
if (!checkCourseCompletion($course_id, $learner_id)) {
    echo json_encode(['success' => false, 'message' => 'Course not yet completed']);
    exit();
}

// Award the NFT certificate
$result = awardNFTCertificate($course_id, $learner_id);

echo json_encode($result);
?>
