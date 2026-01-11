<?php
session_start();
include 'db.php';

// Check if user is logged in as creator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: login.html');
    exit();
}

// Get creator data
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM creators WHERE id = '$user_id'";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $creator = mysqli_fetch_assoc($result);
} else {
    echo "<script>alert('Creator not found'); window.location.href='login.html';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Profile - NFT Learning Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Same CSS as learner profile with creator-specific modifications */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #121212 0%, #1a1a1a 100%);
            min-height: 100vh;
            color: #e0e0e0;
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
            background: #1e1e1e;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-bottom: 2rem;
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
            background: linear-gradient(135deg, #4a90e2, #357abd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: white;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
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
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #28a745;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.9rem;
            margin-top: 0.5rem;
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
            border-left: 4px solid #4a90e2;
        }

        .detail-label {
            font-weight: bold;
            color: #b0b0b0;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            font-size: 1.1rem;
            color: #ffffff;
            word-break: break-all;
        }

        .stats-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            border-radius: 15px;
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

        .bio-section {
            background: #1e1e1e;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-bottom: 2rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .social-link {
            padding: 0.5rem 1rem;
            background: #4a90e2;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .social-link:hover {
            background: #357abd;
        }

        #profilePictureInput {
            display: none;
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
              <a href="#" class="logo">Learnity</a>
            <ul class="nav-links">
                <li><a href="home-creator.php">Home</a></li>
                <li><a href="course-browser-creator.php">Courses</a></li>
                <li><a href="course-management.php">Course Management</a></li>
                <li><a href="creator-profile.php">Profile</a></li>
                <li><a href="nft-search.php" class="nav-link">Search NFT</a></li>
                <li><a href="login.html">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar" onclick="document.getElementById('profilePictureInput').click()">
                    <?php if (!empty($creator['profile_picture']) && file_exists($creator['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($creator['profile_picture']); ?>" alt="Profile Picture" id="profileImage">
                    <?php else: ?>
                        <i class="fas fa-user" id="defaultIcon"></i>
                    <?php endif; ?>
                    <div class="edit-overlay">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h1><?php echo htmlspecialchars($creator['full_name']); ?></h1>
                    <p style="color: #b0b0b0; font-size: 1.1rem;">Course Creator</p>
                    <?php if ($creator['is_verified']): ?>
                        <div class="verified-badge">
                            <i class="fas fa-check-circle"></i>
                            Verified Creator
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="profile-details">
                <div class="detail-item">
                    <div class="detail-label">Creator ID</div>
                    <div class="detail-value">#<?php echo str_pad($creator['id'], 6, '0', STR_PAD_LEFT); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Email Address</div>
                    <div class="detail-value"><?php echo htmlspecialchars($creator['email']); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Expertise</div>
                    <div class="detail-value">
                        <?php 
                        if (!empty($creator['expertise'])) {
                            echo htmlspecialchars($creator['expertise']);
                        } else {
                            echo '<span style="color: #888888; font-style: italic;">Not specified</span>';
                        }
                        ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Wallet Address</div>
                    <div class="detail-value">
                        <?php 
                        if (!empty($creator['wallet_address'])) {
                            echo htmlspecialchars($creator['wallet_address']);
                        } else {
                            echo '<span style="color: #888888; font-style: italic;">Not Connected</span>';
                        }
                        ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Member Since</div>
                    <div class="detail-value">
                        <?php 
                        $date = new DateTime($creator['created_at']);
                        echo $date->format('F j, Y');
                        ?>
                    </div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Total Reviews</div>
                    <div class="detail-value"><?php echo $creator['total_reviews']; ?> reviews</div>
                </div>
            </div>
        </div>

        <?php if (!empty($creator['bio'])): ?>
        <div class="bio-section">
            <h2 style="margin-bottom: 1rem; color: #ffffff;">About Me</h2>
            <p style="color: #b0b0b0; line-height: 1.6;"><?php echo nl2br(htmlspecialchars($creator['bio'])); ?></p>
            
            <?php if (!empty($creator['social_linkedin']) || !empty($creator['social_twitter']) || !empty($creator['social_website'])): ?>
            <div class="social-links">
                <?php if (!empty($creator['social_linkedin'])): ?>
                    <a href="<?php echo htmlspecialchars($creator['social_linkedin']); ?>" class="social-link" target="_blank">
                        <i class="fab fa-linkedin"></i> LinkedIn
                    </a>
                <?php endif; ?>
                <?php if (!empty($creator['social_twitter'])): ?>
                    <a href="<?php echo htmlspecialchars($creator['social_twitter']); ?>" class="social-link" target="_blank">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                <?php endif; ?>
                <?php if (!empty($creator['social_website'])): ?>
                    <a href="<?php echo htmlspecialchars($creator['social_website']); ?>" class="social-link" target="_blank">
                        <i class="fas fa-globe"></i> Website
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="stats-section">
            <h2 style="margin-bottom: 2rem; color: #ffffff;">Teaching Statistics</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $creator['total_courses']; ?></div>
                    <div class="stat-label">Courses Created</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $creator['total_students']; ?></div>
                    <div class="stat-label">Students Taught</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden file input -->
    <input type="file" id="profilePictureInput" accept="image/*" onchange="uploadProfilePicture(this)">

    <script>
        function uploadProfilePicture(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validate file size (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size must be less than 5MB');
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Only JPG, PNG, GIF, and WebP files are allowed');
                    return;
                }
                
                // Create FormData
                const formData = new FormData();
                formData.append('profile_picture', file);
                
                // Upload file
                fetch('upload_creator_profile_picture.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update profile picture display
                        location.reload(); // Simple reload for now
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    alert('Upload failed. Please try again.');
                    console.error('Error:', error);
                });
            }
        }
    </script>
</body>
</html>