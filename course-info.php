<?php
session_start();
include 'db.php';

// Get course ID from URL parameter
$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($course_id <= 0) {
    header('Location: course-browser.php');
    exit();
}

// Get course details from database
$course_query = "SELECT 
    c.id,
    c.course_name as title,
    c.description,
    c.category,
    c.price,
    c.duration,
    c.students_enrolled,
    c.rating,
    c.total_reviews,
    c.status,
    c.video_path,
    c.thumbnail,
    c.created_at,
    c.updated_at,
    cr.full_name as instructor_name,
    cr.id as creator_id,
    cr.profile_picture as creator_profile_picture
FROM courses c 
LEFT JOIN creators cr ON c.creator_id = cr.id 
WHERE c.id = ? AND c.status = 'published'
LIMIT 1";

$stmt = mysqli_prepare($conn, $course_query);
mysqli_stmt_bind_param($stmt, 'i', $course_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$course = mysqli_fetch_assoc($result);

if (!$course) {
    header('Location: course-browser.php');
    exit();
}

// Check if user is logged in and get user info
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$is_enrolled = false;

// Check if learner is enrolled in this course
if ($is_logged_in && $user_role === 'learner') {
    $enrollment_query = "SELECT id FROM enrollments WHERE learner_id = ? AND course_id = ?";
    $enrollment_stmt = mysqli_prepare($conn, $enrollment_query);
    mysqli_stmt_bind_param($enrollment_stmt, 'ii', $user_id, $course_id);
    mysqli_stmt_execute($enrollment_stmt);
    $enrollment_result = mysqli_stmt_get_result($enrollment_stmt);
    $is_enrolled = mysqli_num_rows($enrollment_result) > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['title']); ?> - EduChain</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
        }

        /* Navigation Bar */
        .navbar {
            position: sticky;
            top: 0;
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            padding: 0 20px;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #2c5aa0;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo:hover {
            color: #1e3f73;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 25px;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            position: relative;
            transition: color 0.3s ease;
            padding: 8px 16px;
            border-radius: 20px;
        }

        .nav-link:hover {
            color: #2c5aa0;
            background: rgba(44, 90, 160, 0.1);
        }

        .nav-link.active {
            color: #2c5aa0;
            background: rgba(44, 90, 160, 0.15);
            font-weight: 600;
        }

        .logout-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        /* Main Content */
        main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .course-header {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
            align-items: start;
        }

        .course-info-content {
            display: flex;
            flex-direction: column;
        }

        .course-thumbnail-display {
            width: 100%;
            height: 200px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .course-thumbnail-display img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-thumbnail-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
        }

        .course-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3f73;
            margin-bottom: 1rem;
            line-height: 1.2;
        }

        .course-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
        }

        .meta-item i {
            color: #2c5aa0;
        }

        .course-description {
            font-size: 1.1rem;
            color: #555;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .course-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3f73 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(44, 90, 160, 0.3);
        }

        .btn-enrolled {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            cursor: default;
        }

        .btn-secondary {
            background: white;
            color: #2c5aa0;
            border: 2px solid #2c5aa0;
        }

        .btn-secondary:hover {
            background: #2c5aa0;
            color: white;
        }

        .course-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c5aa0;
        }

        .course-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }

        .main-content {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e3f73;
            margin-bottom: 1rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }

        .instructor-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .instructor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            overflow: hidden;
            position: relative;
        }

        .instructor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .instructor-avatar .avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }

        .instructor-details h4 {
            color: #1e3f73;
            margin-bottom: 0.25rem;
        }

        .instructor-details p {
            color: #666;
            font-size: 0.9rem;
        }

        .course-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c5aa0;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .video-preview {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            margin-bottom: 1.5rem;
        }

        .requirements, .what-you-learn {
            margin-bottom: 2rem;
        }

        .requirements ul, .what-you-learn ul {
            list-style: none;
            padding-left: 0;
        }

        .requirements li, .what-you-learn li {
            padding: 0.5rem 0;
            padding-left: 1.5rem;
            position: relative;
        }

        .requirements li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }

        .what-you-learn li:before {
            content: "🎯";
            position: absolute;
            left: 0;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .course-content {
                grid-template-columns: 1fr;
            }
            
            .course-meta {
                flex-direction: column;
                gap: 1rem;
            }
            
            .course-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .course-title {
                font-size: 2rem;
            }
            
            main {
                padding: 1rem;
            }
            
            .course-header {
                padding: 2rem;
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .course-thumbnail-display {
                order: -1;
                height: 250px;
            }
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #333;
            margin: 3px 0;
            transition: 0.3s;
            border-radius: 2px;
        }

        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: white;
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                gap: 30px;
                padding-top: 50px;
                transition: left 0.3s ease;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            }

            .nav-menu.active {
                left: 0;
            }

            .hamburger {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">EduChain</a>
            
            <ul class="nav-menu" id="navMenu">
                <?php if ($is_logged_in && $user_role === 'learner'): ?>
                    <li><a href="home-learner.php" class="nav-link">Home</a></li>
                    <li><a href="course-browser.php" class="nav-link">Courses</a></li>
                    <li><a href="nft-search.php" class="nav-link">Search NFT</a></li>
                    <li><a href="learner-profile.php" class="nav-link">Profile</a></li>
                    <li><a href="login.html" class="nav-link logout-btn">Logout</a></li>
                <?php else: ?>
                    <li><a href="guest.php" class="nav-link">Home</a></li>
                    <li><a href="course-browser.php" class="nav-link">Courses</a></li>
                    <li><a href="login.html" class="nav-link">Login</a></li>
                    <li><a href="register.html" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>

            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Course Header -->
        <div class="course-header">
            <div class="course-info-content">
                <h1 class="course-title"><?php echo htmlspecialchars($course['title']); ?></h1>
                
                <div class="course-meta">
                    <div class="meta-item">
                        <i class="fas fa-user-tie"></i>
                        <span>By <?php echo htmlspecialchars($course['instructor_name'] ?? 'Unknown Instructor'); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-tag"></i>
                        <span><?php echo htmlspecialchars($course['category']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?php echo intval($course['duration']); ?> hours</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <span><?php echo intval($course['students_enrolled']); ?> students</span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-star"></i>
                        <span><?php echo number_format($course['rating'], 1); ?> (<?php echo intval($course['total_reviews']); ?> reviews)</span>
                    </div>
                </div>

                <p class="course-description">
                    <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                </p>

                <div class="course-actions">
                    <?php if ($is_logged_in && $user_role === 'learner'): ?>
                        <?php if ($is_enrolled): ?>
                            <a href="course-watching.php?course_id=<?php echo $course['id']; ?>" class="btn btn-enrolled">
                                <i class="fas fa-play"></i> Continue Learning
                            </a>
                        <?php else: ?>
                            <button class="btn btn-primary" id="enrollBtn" onclick="enrollInCourse(<?php echo $course['id']; ?>)">
                                <i class="fas fa-plus"></i> Enroll Now
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.html" class="btn btn-primary">
                            <i class="fas fa-sign-in-alt"></i> Login to Enroll
                        </a>
                    <?php endif; ?>
                    
                    <div class="course-price">
                        <?php echo $course['price'] == 0 ? 'Free' : '$' . number_format($course['price'], 2); ?>
                    </div>
                </div>
            </div>

            <!-- Course Thumbnail -->
            <div class="course-thumbnail-display">
                <?php if (!empty($course['thumbnail'])): ?>
                    <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" />
                <?php else: ?>
                    <div class="course-thumbnail-placeholder">
                        📚
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Course Content -->
        <div class="course-content">
            <!-- Main Content -->
            <div class="main-content">
                <h2 class="section-title">Course Content</h2>
                
                <?php if (!empty($course['thumbnail'])): ?>
                    <div class="video-preview">
                        <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 15px;">
                    </div>
                <?php elseif (!empty($course['video_path'])): ?>
                    <div class="video-preview">
                        <i class="fas fa-play-circle"></i>
                    </div>
                <?php else: ?>
                    <div class="video-preview">
                        📚
                    </div>
                <?php endif; ?>

                <div class="what-you-learn">
                    <h3 class="section-title">What you'll learn</h3>
                    <ul>
                        <li>Master the fundamentals covered in this course</li>
                        <li>Apply practical skills through hands-on exercises</li>
                        <li>Build real-world projects to enhance your portfolio</li>
                        <li>Gain confidence in the subject matter</li>
                        <li>Receive a certificate upon completion</li>
                    </ul>
                </div>

                <div class="requirements">
                    <h3 class="section-title">Requirements</h3>
                    <ul>
                        <li>Basic computer skills and internet access</li>
                        <li>Enthusiasm to learn and practice</li>
                        <li>No prior experience required - we'll teach you everything</li>
                    </ul>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <h3 class="section-title">Instructor</h3>
                <div class="instructor-info">
                    <div class="instructor-avatar">
                        <?php if (!empty($course['creator_profile_picture']) && file_exists($course['creator_profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($course['creator_profile_picture']); ?>" alt="<?php echo htmlspecialchars($course['instructor_name'] ?? 'Instructor'); ?> Profile Picture">
                        <?php else: ?>
                            <div class="avatar-fallback">
                                <?php echo strtoupper(substr($course['instructor_name'] ?? 'I', 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="instructor-details">
                        <h4><?php echo htmlspecialchars($course['instructor_name'] ?? 'Unknown Instructor'); ?></h4>
                        <p>Course Creator</p>
                    </div>
                </div>

                <h3 class="section-title">Course Stats</h3>
                <div class="course-stats">
                    <div class="stat-item">
                        <div class="stat-value"><?php echo intval($course['students_enrolled']); ?></div>
                        <div class="stat-label">Students</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo intval($course['duration']); ?>h</div>
                        <div class="stat-label">Duration</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo number_format($course['rating'], 1); ?></div>
                        <div class="stat-label">Rating</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value"><?php echo intval($course['total_reviews']); ?></div>
                        <div class="stat-label">Reviews</div>
                    </div>
                </div>

                <?php if ($is_logged_in && $user_role === 'learner' && !$is_enrolled): ?>
                    <button class="btn btn-primary" style="width: 100%; margin-top: 1rem;" onclick="enrollInCourse(<?php echo $course['id']; ?>)">
                        <i class="fas fa-plus"></i> Enroll in Course
                    </button>
                <?php elseif ($is_logged_in && $user_role === 'learner' && $is_enrolled): ?>
                    <a href="course-watching.php?course_id=<?php echo $course['id']; ?>" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                        <i class="fas fa-play"></i> Watch Course
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        // Mobile menu functionality
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Enrollment functionality
        async function enrollInCourse(courseId) {
            const enrollBtn = document.getElementById('enrollBtn');
            
            if (!enrollBtn) return;
            
            enrollBtn.disabled = true;
            enrollBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enrolling...';

            try {
                const response = await fetch('enroll_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ course_id: courseId })
                });

                const data = await response.json();

                if (data.success) {
                    // Update button to "Watch Now"
                    enrollBtn.innerHTML = '<i class="fas fa-play"></i> Watch Now';
                    enrollBtn.className = 'btn btn-primary';
                    enrollBtn.disabled = false;
                    
                    // Change button functionality to redirect to course watching
                    enrollBtn.onclick = function() {
                        window.location.href = `course-watching.php?course_id=${courseId}`;
                    };
                    
                    // Also update any sidebar enroll button if it exists
                    const sidebarBtn = document.querySelector('.sidebar .btn-primary');
                    if (sidebarBtn && sidebarBtn.textContent.includes('Enroll')) {
                        sidebarBtn.innerHTML = '<i class="fas fa-play"></i> Watch Now';
                        sidebarBtn.onclick = function() {
                            window.location.href = `course-watching.php?course_id=${courseId}`;
                        };
                    }
                    
                } else {
                    enrollBtn.innerHTML = '<i class="fas fa-plus"></i> Enroll Now';
                    enrollBtn.disabled = false;
                    alert(data.message || 'Enrollment failed. Please try again.');
                }
            } catch (error) {
                enrollBtn.innerHTML = '<i class="fas fa-plus"></i> Enroll Now';
                enrollBtn.disabled = false;
                alert('Network error. Please try again.');
                console.error('Enrollment error:', error);
            }
        }
    </script>
</body>
</html>
