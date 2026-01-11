<?php
session_start();
include 'db.php';
include 'nft_certificate_system.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.html');
    exit();
}

// Get learner data
$user_id = $_SESSION['user_id'];
$sql = "SELECT id, full_name, email, wallet_address, created_at, profile_picture FROM learners WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $learner = mysqli_fetch_assoc($result);
} else {
    echo "<script>alert('User not found'); window.location.href='login.html';</script>";
    exit();
}

// Get statistics
$certificates = getLearnerCertificates($user_id);
$certificate_count = count($certificates);

// Get completed courses count
$completed_query = "SELECT COUNT(*) as count FROM enrollments WHERE learner_id = ? AND completed = 1";
$stmt = $conn->prepare($completed_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_result = $stmt->get_result();
$completed_courses = $completed_result->fetch_assoc()['count'];

// Get total enrolled courses count
$enrolled_query = "SELECT COUNT(*) as count FROM enrollments WHERE learner_id = ?";
$stmt = $conn->prepare($enrolled_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_result = $stmt->get_result();
$enrolled_courses = $enrolled_result->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Learnity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            min-height: 100vh;
            color: #e1e5e9;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .profile-card {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            margin-bottom: 2rem;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
            border: 3px solid rgba(220, 38, 38, 0.5);
        }

        .profile-avatar:hover {
            transform: scale(1.05);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .profile-avatar .edit-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            border-radius: 50%;
        }

        .profile-avatar:hover .edit-overlay {
            opacity: 1;
        }

        .edit-overlay i {
            color: white;
            font-size: 1.5rem;
        }

        .profile-info h1 {
            font-size: 2.5rem;
            color: #f8f9fa;
            margin-bottom: 0.5rem;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .detail-item {
            background: #2a2a2a;
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 4px solid #dc2626;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .detail-label {
            font-weight: bold;
            color: #d1d5db;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #f8f9fa;
            word-break: break-all;
        }

        .stats-section {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.4);
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            border-radius: 15px;
            border: 1px solid rgba(220, 38, 38, 0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.4);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        /* File input styling */
        #profilePictureInput {
            display: none;
        }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #374151;
            border-top: 5px solid #ef4444;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Success/Error messages */
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 2rem;
            border-radius: 10px;
            color: white;
            font-weight: bold;
            z-index: 1001;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        }

        .message.success {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            border: 1px solid rgba(5, 150, 105, 0.3);
        }

        .message.error {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .message.show {
            opacity: 1;
            transform: translateX(0);
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-details {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 0 1rem;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">🎓 Learnity</div>
            <ul class="nav-links">
                <li><a href="home-learner.php">Home</a></li>
                <li><a href="course-browser.php">Courses</a></li>
                <li><a href="learner-profile.php">Profile</a></li>
                <li><a href="my_certificates.php">My Certificates</a></li>
                <li><a href="nft-search.php">Search NFT</a></li>
                <li><a href="login.html">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar" onclick="document.getElementById('profilePictureInput').click()">
                    <?php if (!empty($learner['profile_picture']) && file_exists($learner['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($learner['profile_picture']); ?>" alt="Profile Picture" id="profileImage">
                    <?php else: ?>
                        <i class="fas fa-user" id="defaultIcon"></i>
                    <?php endif; ?>
                    <div class="edit-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($learner['full_name']); ?></h1>
                    <p style="color: #d1d5db; font-size: 1.1rem;">Student Profile</p>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-item">
                    <div class="detail-label">Student ID</div>
                    <div class="detail-value">#<?php echo str_pad($learner['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Email Address</div>
                    <div class="detail-value"><?php echo htmlspecialchars($learner['email']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Wallet Address</div>
                    <div class="detail-value">
                        <?php 
                        if (!empty($learner['wallet_address'])) {
                            echo htmlspecialchars($learner['wallet_address']);
                        } else {
                            echo '<span style="color: #9ca3af; font-style: italic;">Not Connected</span>';
                        }
                        ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Member Since</div>
                    <div class="detail-value">
                        <?php 
                        $date = new DateTime($learner['created_at']);
                        echo $date->format('F j, Y');
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-section">
            <h2 style="margin-bottom: 2rem; color: #f8f9fa;">Learning Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $completed_courses; ?></div>
                    <div class="stat-label">Courses Completed</div>
                </div>
                <div class="stat-card" onclick="window.location.href='my_certificates.php'" style="cursor: pointer;">
                    <div class="stat-number"><?php echo $certificate_count; ?></div>
                    <div class="stat-label">NFT Certificates</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $enrolled_courses; ?></div>
                    <div class="stat-label">Courses Enrolled</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden file input -->
    <input type="file" id="profilePictureInput" accept="image/*" onchange="uploadProfilePicture(this)">

    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
        function uploadProfilePicture(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showMessage('File size must be less than 5MB', 'error');
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    showMessage('Only JPG, PNG, GIF, and WebP files are allowed', 'error');
                    return;
                }
                
                // Show loading
                document.getElementById('loadingOverlay').style.display = 'flex';
                
                // Create FormData
                const formData = new FormData();
                formData.append('profile_picture', file);
                
                // Upload file
                fetch('upload_profile_picture.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    
                    if (data.success) {
                        // Update profile picture display
                        const avatar = document.querySelector('.profile-avatar');
                        const existingImg = avatar.querySelector('img');
                        const defaultIcon = avatar.querySelector('#defaultIcon');
                        
                        if (existingImg) {
                            existingImg.src = data.image_path + '?t=' + new Date().getTime();
                        } else {
                            // Remove default icon and add image
                            if (defaultIcon) defaultIcon.remove();
                            const img = document.createElement('img');
                            img.src = data.image_path + '?t=' + new Date().getTime();
                            img.alt = 'Profile Picture';
                            img.id = 'profileImage';
                            avatar.insertBefore(img, avatar.querySelector('.edit-overlay'));
                        }
                        
                        showMessage(data.message, 'success');
                    } else {
                        showMessage(data.message, 'error');
                    }
                })
                .catch(error => {
                    document.getElementById('loadingOverlay').style.display = 'none';
                    showMessage('Upload failed. Please try again.', 'error');
                    console.error('Error:', error);
                });
            }
        }
        
        function showMessage(text, type) {
            // Remove existing messages
            const existingMessages = document.querySelectorAll('.message');
            existingMessages.forEach(msg => msg.remove());
            
            // Create new message
            const message = document.createElement('div');
            message.className = `message ${type}`;
            message.textContent = text;
            document.body.appendChild(message);
            
            // Show message
            setTimeout(() => message.classList.add('show'), 100);
            
            // Hide message after 3 seconds
            setTimeout(() => {
                message.classList.remove('show');
                setTimeout(() => message.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>