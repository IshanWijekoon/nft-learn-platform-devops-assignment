<?php
/**
 * NFT Certificate Generator
 * Handles NFT certificate creation and awarding when learners complete courses
 */

include 'db.php';

/**
 * Generate a unique NFT key for the certificate
 */
function generateNFTKey() {
    return 'NFT' . strtoupper(bin2hex(random_bytes(16))) . time();
}

/**
 * Generate a unique certificate hash for verification
 */
function generateCertificateHash($course_id, $learner_id, $nft_key) {
    $data = $course_id . '|' . $learner_id . '|' . $nft_key . '|' . time();
    return hash('sha256', $data);
}

/**
 * Generate a verification code for public certificate verification
 */
function generateVerificationCode() {
    return strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
}

/**
 * Award NFT certificate to learner upon course completion
 */
function awardNFTCertificate($course_id, $learner_id) {
    global $conn;
    
    try {
        // Start transaction
        $conn->autocommit(false);
        
        // Check if certificate already exists
        $check_stmt = $conn->prepare("SELECT id FROM nft_certificates WHERE course_id = ? AND learner_id = ?");
        $check_stmt->bind_param("ii", $course_id, $learner_id);
        $check_stmt->execute();
        $existing_result = $check_stmt->get_result();
        
        if ($existing_result->num_rows > 0) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Certificate already awarded for this course'];
        }
        
        // Get course and learner information
        $course_stmt = $conn->prepare("
            SELECT c.course_name, c.creator_id, c.nft_certificate_image, cr.full_name as creator_name 
            FROM courses c 
            JOIN creators cr ON c.creator_id = cr.id 
            WHERE c.id = ?
        ");
        $course_stmt->bind_param("i", $course_id);
        $course_stmt->execute();
        $course_result = $course_stmt->get_result();
        
        if ($course_result->num_rows === 0) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Course not found'];
        }
        
        $course = $course_result->fetch_assoc();
        
        // Get learner information
        $learner_stmt = $conn->prepare("SELECT full_name FROM learners WHERE id = ?");
        $learner_stmt->bind_param("i", $learner_id);
        $learner_stmt->execute();
        $learner_result = $learner_stmt->get_result();

        if ($learner_result->num_rows === 0) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Learner not found'];
        }        $learner = $learner_result->fetch_assoc();
        
        // Check if course has NFT certificate template
        if (empty($course['nft_certificate_image'])) {
            $conn->rollback();
            error_log("No NFT certificate template for course ID: $course_id");
            return ['success' => false, 'message' => 'No NFT certificate template found for this course. Please contact the course creator.'];
        }
        
        // Check if certificate template file exists
        if (!file_exists($course['nft_certificate_image'])) {
            error_log("Certificate template file not found: " . $course['nft_certificate_image']);
            return ['success' => false, 'message' => 'Certificate template file is missing. Please contact the course creator.'];
        }
        
        // Generate unique identifiers
        $nft_key = generateNFTKey();
        $certificate_hash = generateCertificateHash($course_id, $learner_id, $nft_key);
        $verification_code = generateVerificationCode();
        
        // Create certificate record
        $cert_stmt = $conn->prepare("
            INSERT INTO nft_certificates 
            (course_id, learner_id, creator_id, nft_key, certificate_hash, learner_name, course_name, creator_name, certificate_image_path, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'issued')
        ");
        
        $cert_stmt->bind_param("iiissssss", 
            $course_id, 
            $learner_id, 
            $course['creator_id'], 
            $nft_key, 
            $certificate_hash, 
            $learner['full_name'], 
            $course['course_name'], 
            $course['creator_name'], 
            $course['nft_certificate_image']
        );
        
        if (!$cert_stmt->execute()) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Failed to create certificate record'];
        }
        
        $certificate_id = $cert_stmt->insert_id;
        
        // Create verification record
        $verify_stmt = $conn->prepare("
            INSERT INTO nft_verifications (certificate_id, verification_code) 
            VALUES (?, ?)
        ");
        $verify_stmt->bind_param("is", $certificate_id, $verification_code);
        $verify_stmt->execute();
        
        // Update enrollment record to mark certificate as issued
        $enroll_stmt = $conn->prepare("
            UPDATE enrollments 
            SET certificate_issued = 1, completion_date = NOW() 
            WHERE course_id = ? AND learner_id = ?
        ");
        $enroll_stmt->bind_param("ii", $course_id, $learner_id);
        $enroll_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Clean up statements
        $check_stmt->close();
        $course_stmt->close();
        $learner_stmt->close();
        $cert_stmt->close();
        $verify_stmt->close();
        $enroll_stmt->close();
        
        return [
            'success' => true,
            'message' => 'NFT certificate awarded successfully',
            'certificate_id' => $certificate_id,
            'nft_key' => $nft_key,
            'certificate_hash' => $certificate_hash,
            'verification_code' => $verification_code,
            'verification_url' => "verify_certificate.php?code=" . $verification_code
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        error_log("NFT Certificate Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Error awarding certificate: ' . $e->getMessage()];
    } finally {
        $conn->autocommit(true);
    }
}

/**
 * Check if learner has completed all course requirements
 */
function checkCourseCompletion($course_id, $learner_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT completed, progress 
        FROM enrollments 
        WHERE course_id = ? AND learner_id = ?
    ");
    $stmt->bind_param("ii", $course_id, $learner_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $enrollment = $result->fetch_assoc();
    $stmt->close();
    
    // Consider course completed if marked as completed OR progress is 100%
    return ($enrollment['completed'] == 1 || $enrollment['progress'] >= 100.0);
}

/**
 * Get all certificates for a learner
 */
function getLearnerCertificates($learner_id) {
    global $conn;
    
    try {
        // Check if tables exist first
        $tables_check = mysqli_query($conn, "SHOW TABLES LIKE 'nft_certificates'");
        if (mysqli_num_rows($tables_check) == 0) {
            error_log("NFT certificates table does not exist");
            return [];
        }
        
        $stmt = $conn->prepare("
            SELECT nc.*, nv.verification_code, nv.verification_count, c.course_name, c.category
            FROM nft_certificates nc
            LEFT JOIN nft_verifications nv ON nc.id = nv.certificate_id
            LEFT JOIN courses c ON nc.course_id = c.id
            WHERE nc.learner_id = ?
            ORDER BY nc.issued_at DESC
        ");
        
        if (!$stmt) {
            error_log("Failed to prepare certificate query: " . mysqli_error($conn));
            return [];
        }
        
        $stmt->bind_param("i", $learner_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $certificates = [];
        while ($row = $result->fetch_assoc()) {
            $certificates[] = $row;
        }
        
        $stmt->close();
        return $certificates;
        
    } catch (Exception $e) {
        error_log("Error in getLearnerCertificates: " . $e->getMessage());
        return [];
    }
}

/**
 * Verify certificate by verification code
 */
function verifyCertificate($verification_code) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT nc.*, nv.verification_count, nv.created_at as verification_created,
               c.course_name, l.full_name as learner_name
        FROM nft_verifications nv
        JOIN nft_certificates nc ON nv.certificate_id = nc.id
        JOIN courses c ON nc.course_id = c.id
        JOIN learners l ON nc.learner_id = l.id
        WHERE nv.verification_code = ?
    ");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['success' => false, 'message' => 'Invalid verification code'];
    }
    
    $certificate = $result->fetch_assoc();
    
    // Update verification count
    $update_stmt = $conn->prepare("
        UPDATE nft_verifications 
        SET verification_count = verification_count + 1, verified_at = NOW(), verifier_ip = ?
        WHERE verification_code = ?
    ");
    $verifier_ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $update_stmt->bind_param("ss", $verifier_ip, $verification_code);
    $update_stmt->execute();
    
    $stmt->close();
    $update_stmt->close();
    
    return [
        'success' => true,
        'certificate' => $certificate
    ];
}
?>
