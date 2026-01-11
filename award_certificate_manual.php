<?php
session_start();
include 'db.php';
include 'nft_certificate_system.php';

// Check if user is logged in as admin or creator
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'creator')) {
    die("Access denied. Admin or creator access required.");
}

echo "<h2>Manual NFT Certificate Award</h2>";


    if ($result['success']) {
        echo "<div style='background: green; color: white; padding: 1rem; border-radius: 5px; margin: 1rem 0;'>";
        echo "<h3>✓ Certificate Awarded Successfully!</h3>";
        echo "<p><strong>NFT Key:</strong> {$result['nft_key']}</p>";
        echo "<p><strong>Verification Code:</strong> {$result['verification_code']}</p>";
        echo "<p><strong>Certificate ID:</strong> {$result['certificate_id']}</p>";
        echo "</div>";
    } else {
        echo "<div style='background: red; color: white; padding: 1rem; border-radius: 5px; margin: 1rem 0;'>";
        echo "<h3>✗ Certificate Award Failed</h3>";
        echo "<p><strong>Error:</strong> {$result['message']}</p>";
        echo "</div>";
    }
}

// Get courses with certificates
$courses_query = "SELECT c.id, c.course_name, c.nft_certificate_image, 
                         COUNT(e.id) as enrollments,
                         COUNT(CASE WHEN e.completed = 1 THEN 1 END) as completed_enrollments
                  FROM courses c 
                  LEFT JOIN enrollments e ON c.id = e.course_id
                  WHERE c.nft_certificate_image IS NOT NULL AND c.nft_certificate_image != ''
                  GROUP BY c.id
                  ORDER BY c.id DESC";
$courses_result = mysqli_query($conn, $courses_query);

// Get learners
$learners_query = "SELECT id, full_name FROM learners ORDER BY full_name";
$learners_result = mysqli_query($conn, $learners_query);
?>

<form method="POST" style="background: #f0f0f0; padding: 1rem; border-radius: 5px; margin: 1rem 0;">
    <h3>Award Certificate Manually</h3>
    
    <div style="margin: 1rem 0;">
        <label for="course_id"><strong>Select Course:</strong></label><br>
        <select name="course_id" id="course_id" required style="width: 100%; padding: 0.5rem;">
            <option value="">-- Select Course --</option>
            <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                <option value="<?php echo $course['id']; ?>">
                    <?php echo htmlspecialchars($course['course_name']); ?> 
                    (ID: <?php echo $course['id']; ?>, Completed: <?php echo $course['completed_enrollments']; ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <div style="margin: 1rem 0;">
        <label for="learner_id"><strong>Select Learner:</strong></label><br>
        <select name="learner_id" id="learner_id" required style="width: 100%; padding: 0.5rem;">
            <option value="">-- Select Learner --</option>
            <?php while ($learner = mysqli_fetch_assoc($learners_result)): ?>
                <option value="<?php echo $learner['id']; ?>">
                    <?php echo htmlspecialchars($learner['full_name']); ?> (ID: <?php echo $learner['id']; ?>)
                </option>
            <?php endwhile; ?>
        </select>
    </div>
    
    <button type="submit" style="background: #007cba; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer;">
        Award Certificate
    </button>
</form>

<p><a href="test_nft_system.php" style="color: blue;">Test NFT System</a> | 
   <a href="setup_nft_tables.php" style="color: blue;">Setup NFT Tables</a> | 
   <a href="course-management.php" style="color: blue;">Course Management</a></p>
