<?php
// Start output buffering to prevent any unexpected output
ob_start();

session_start();
include 'db.php';

// Turn off all error reporting to prevent any output before HTML
error_reporting(0);
ini_set('display_errors', 0);

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.html');
    exit();
}

// Get course ID from URL parameter
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$learner_id = $_SESSION['user_id'];

// Get course details with creator profile picture
$course_query = "SELECT c.*, cr.full_name as creator_name, cr.profile_picture as creator_profile_picture 
                 FROM courses c 
                 JOIN creators cr ON c.creator_id = cr.id 
                 WHERE c.id = '$course_id'";
$course_result = mysqli_query($conn, $course_query);

if (!$course_result || mysqli_num_rows($course_result) === 0) {
    echo "<script>alert('Course not found'); window.location.href='course-browser.php';</script>";
    exit();
}

$course = mysqli_fetch_assoc($course_result);

// Ensure required fields exist with defaults
$course['duration'] = $course['duration'] ?? 'Not specified';
$course['level'] = $course['level'] ?? 'Beginner';
$course['category'] = $course['category'] ?? 'General';
$course['description'] = $course['description'] ?? 'No description available';

// Fix video path if it exists but doesn't include full path
if (!empty($course['video_path']) && !file_exists($course['video_path'])) {
    // Try to find the video file in the uploads directory
    $video_pattern = "uploads/course_videos/course_{$course_id}_*.mp4";
    $matching_files = glob($video_pattern);
    if (!empty($matching_files)) {
        $course['video_path'] = $matching_files[0];
        // Update database with correct path
        $update_video_path = "UPDATE courses SET video_path = '{$course['video_path']}' WHERE id = '$course_id'";
        mysqli_query($conn, $update_video_path);
    }
}

// Check if learner is enrolled in this course
$enrollment_query = "SELECT * FROM enrollments WHERE learner_id = '$learner_id' AND course_id = '$course_id'";
$enrollment_result = mysqli_query($conn, $enrollment_query);
$is_enrolled = mysqli_num_rows($enrollment_result) > 0;
$enrollment = $is_enrolled ? mysqli_fetch_assoc($enrollment_result) : null;

// If not enrolled, create enrollment record
if (!$is_enrolled) {
    $enroll_query = "INSERT INTO enrollments (learner_id, course_id, enrolled_at) VALUES ('$learner_id', '$course_id', NOW())";
    if (mysqli_query($conn, $enroll_query)) {
        // Update course students count
        $update_students = "UPDATE courses SET students_enrolled = COALESCE(students_enrolled, 0) + 1 WHERE id = '$course_id'";
        mysqli_query($conn, $update_students);
        
        // Refresh enrollment data
        $enrollment_result = mysqli_query($conn, $enrollment_query);
        $enrollment = mysqli_fetch_assoc($enrollment_result);
        $is_enrolled = true;
    }
}

// Get learner details
$learner_query = "SELECT full_name FROM learners WHERE id = '$learner_id'";
$learner_result = mysqli_query($conn, $learner_query);
$learner = mysqli_fetch_assoc($learner_result);

// Clean any previous output before HTML starts
ob_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($course['course_name']); ?> - Learnity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f0f;
            color: #ffffff;
            min-height: 100vh;
        }

        .navbar {
            background: rgba(0, 0, 0, 0.9);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(10px);
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
            color: #fff;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            transition: color 0.3s;
            padding: 0.5rem 1rem;
            border-radius: 20px;
        }

        .nav-links a:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
        }

        .main-content {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            padding: 2rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: fit-content;
        }

        .course-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #fff;
            font-weight: 700;
        }

        .course-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #ccc;
            font-size: 0.9rem;
        }

        .meta-item i {
            color: #667eea;
        }

        .video-container {
            position: relative;
            width: 100%;
            height: 400px;
            margin-bottom: 2rem;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .course-video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
            background: #000;
        }

        .video-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            border-radius: 15px;
        }

        .video-controls {
            background: rgba(0, 0, 0, 0.8);
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .progress-container {
            flex: 1;
            background: rgba(255, 255, 255, 0.2);
            height: 6px;
            border-radius: 3px;
            cursor: not-allowed;
            position: relative;
        }

        .progress-bar {
            background: linear-gradient(90deg, #667eea, #764ba2);
            height: 100%;
            border-radius: 3px;
            width: 0%;
            transition: width 0.3s ease;
        }

        .completion-message {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            margin-top: 2rem;
            display: none;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .completion-message.show {
            display: block;
        }

        .course-description {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .course-description h3 {
            color: #fff;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .course-description p {
            color: #ccc;
            line-height: 1.6;
        }

        .instructor-info {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .instructor-details {
            display: flex;
            align-items: center;
            gap: 1rem;
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
        }

        .instructor-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #fff;
        }

        .instructor-role {
            color: #ccc;
            font-size: 0.9rem;
        }

        .progress-section {
            background: rgba(255, 255, 255, 0.05);
            padding: 1.5rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .progress-section h3 {
            color: #fff;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        .progress-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
        }

        .stat-label {
            color: #ccc;
            font-size: 0.8rem;
            margin-top: 0.25rem;
        }

        .mark-complete-btn {
            width: 100%;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .mark-complete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .mark-complete-btn:disabled {
            background: #666;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 1rem;
                gap: 1rem;
            }
            
            .video-container {
                height: 250px;
            }
            
            .course-meta {
                flex-direction: column;
                gap: 1rem;
            }
            
            .video-controls {
                flex-wrap: wrap;
            }
            
            .progress-stats {
                grid-template-columns: 1fr;
            }
        }

        /* Tablet Responsive */
        @media (max-width: 1024px) and (min-width: 769px) {
            .video-container {
                height: 300px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="home-learner.php" class="logo">
                <i class="fas fa-graduation-cap"></i> Learnity
            </a>
            <ul class="nav-links">
                <li><a href="course-browser.php"><i class="fas fa-book"></i> Browse Courses</a></li>
                <li><a href="learner-profile.php"><i class="fas fa-user"></i> Profile</a></li>
                <li><a href="login.html"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Main Content -->
        <div class="main-content">
            <h1 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h1>
            
            <div class="course-meta">
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>Instructor: <?php echo htmlspecialchars($course['creator_name']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Duration: <?php echo htmlspecialchars($course['duration']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-layer-group"></i>
                    <span>Category: <?php echo htmlspecialchars($course['category']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-signal"></i>
                    <span>Level: <?php echo htmlspecialchars($course['level']); ?></span>
                </div>
            </div>

            <!-- Video Section -->
            <div class="video-container">
                <?php 
                // Debug video path
                $video_available = !empty($course['video_path']) && file_exists($course['video_path']);
                $video_path = $course['video_path'] ?? '';
                
                if ($video_available): ?>
                    <video id="courseVideo" class="course-video" controls preload="metadata" 
                           onerror="handleVideoError()" onloadeddata="handleVideoLoaded()">
                        <source src="<?php echo htmlspecialchars($video_path); ?>" type="video/mp4">
                        <source src="<?php echo htmlspecialchars($video_path); ?>" type="video/webm">
                        <source src="<?php echo htmlspecialchars($video_path); ?>" type="video/ogg">
                        <p>Your browser doesn't support HTML5 video. <a href="<?php echo htmlspecialchars($video_path); ?>">Download the video</a> instead.</p>
                    </video>
                    
                    <div class="video-controls">
                        <button onclick="togglePlayPause()" id="playPauseBtn" style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; padding: 0.5rem;">
                            <i class="fas fa-play"></i>
                        </button>
                        <div class="progress-container">
                            <div class="progress-bar" id="progressBar"></div>
                        </div>
                        <span id="timeDisplay">0:00 / 0:00</span>
                        <button onclick="toggleFullscreen()" style="background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; padding: 0.5rem;">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                    
                <?php else: ?>
                    <div class="video-placeholder">
                        <div>
                            <i class="fas fa-video" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                            <p>No video available for this course yet.</p>
                            <p style="margin-top: 0.5rem; opacity: 0.7;">The instructor is working on uploading the content.</p>
                            <?php if (!empty($video_path)): ?>
                                <p style="margin-top: 0.5rem; font-size: 0.8rem; color: #ff6b6b;">
                                    Video file not found: <?php echo htmlspecialchars($video_path); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Completion Message -->
            <div class="completion-message" id="completionMessage">
                <i class="fas fa-check-circle" style="font-size: 2rem; margin-bottom: 1rem;"></i>
                <h3>Congratulations!</h3>
                <p>You have successfully completed this course. Great job on your learning journey!</p>
            </div>

            <!-- Course Description -->
            <div class="course-description">
                <h3><i class="fas fa-info-circle"></i> About This Course</h3>
                <p><?php echo nl2br(htmlspecialchars($course['description'])); ?></p>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Instructor Info -->
            <div class="instructor-info">
                <h3 style="color: #fff; margin-bottom: 1rem; font-size: 1.2rem;">
                    <i class="fas fa-chalkboard-teacher"></i> Instructor
                </h3>
                <div class="instructor-details">
                    <div class="instructor-avatar">
                        <?php if (!empty($course['creator_profile_picture']) && file_exists($course['creator_profile_picture'])): ?>
                            <img src="<?php echo htmlspecialchars($course['creator_profile_picture']); ?>" 
                                 alt="<?php echo htmlspecialchars($course['creator_name']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                        <?php else: ?>
                            <?php echo strtoupper(substr($course['creator_name'], 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <div class="instructor-name"><?php echo htmlspecialchars($course['creator_name']); ?></div>
                        <div class="instructor-role">Course Creator</div>
                    </div>
                </div>
            </div>

            <!-- Progress Section -->
            <div class="progress-section">
                <h3><i class="fas fa-chart-line"></i> Your Progress</h3>
                
                <div class="progress-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="watchTime">0%</div>
                        <div class="stat-label">Watched</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="completionStatus">
                            <?php echo (isset($enrollment['completed']) && $enrollment['completed']) ? 'Complete' : 'In Progress'; ?>
                        </div>
                        <div class="stat-label">Status</div>
                    </div>
                </div>

                <div class="progress-container" style="margin: 1rem 0;">
                    <div class="progress-bar" id="courseProgress" style="width: <?php echo (isset($enrollment['completed']) && $enrollment['completed']) ? '100%' : '0%'; ?>"></div>
                </div>

                <?php if (!isset($enrollment['completed']) || !$enrollment['completed']): ?>
                    <button class="mark-complete-btn" onclick="markAsComplete()">
                        <i class="fas fa-check"></i> Mark as Complete
                    </button>
                <?php else: ?>
                    <button class="mark-complete-btn" disabled>
                        <i class="fas fa-trophy"></i> Course Completed!
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        let video = document.getElementById('courseVideo');
        let playPauseBtn = document.getElementById('playPauseBtn');
        let progressBar = document.getElementById('progressBar');
        let timeDisplay = document.getElementById('timeDisplay');
        let watchTimePercentage = 0;
        let isVideoLoaded = false;

        // Video error handling
        function handleVideoError() {
            console.error('Video failed to load');
            const videoContainer = document.querySelector('.video-container');
            if (videoContainer) {
                videoContainer.innerHTML = `
                    <div class="video-placeholder">
                        <div>
                            <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; color: #ff6b6b;"></i>
                            <p>Error loading video</p>
                            <p style="margin-top: 0.5rem; opacity: 0.7;">Please try refreshing the page or contact support.</p>
                            <button onclick="location.reload()" style="background: #667eea; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; margin-top: 1rem; cursor: pointer;">
                                Refresh Page
                            </button>
                        </div>
                    </div>
                `;
            }
        }

        function handleVideoLoaded() {
            isVideoLoaded = true;
            console.log('Video loaded successfully');
            if (timeDisplay) {
                updateTimeDisplay();
            }
        }

        // Video event listeners
        if (video) {
            // Disable right-click context menu to prevent seeking
            video.addEventListener('contextmenu', function(e) {
                e.preventDefault();
            });

            // Disable seeking by preventing timeupdate when user tries to skip
            let lastTime = 0;
            video.addEventListener('timeupdate', function() {
                if (isVideoLoaded) {
                    // Prevent skipping forward (allow only sequential watching)
                    if (video.currentTime > lastTime + 1.5) {
                        video.currentTime = lastTime;
                        showSkipWarning();
                        return;
                    }
                    lastTime = video.currentTime;
                    
                    updateProgress();
                    updateWatchTime();
                }
            });

            video.addEventListener('loadedmetadata', function() {
                isVideoLoaded = true;
                updateTimeDisplay();
                console.log('Video metadata loaded, duration:', video.duration);
            });

            video.addEventListener('play', function() {
                if (playPauseBtn) {
                    playPauseBtn.innerHTML = '<i class="fas fa-pause"></i>';
                }
            });

            video.addEventListener('pause', function() {
                if (playPauseBtn) {
                    playPauseBtn.innerHTML = '<i class="fas fa-play"></i>';
                }
            });

            video.addEventListener('ended', function() {
                // Auto-mark as complete when video ends
                if (!<?php echo isset($enrollment['completed']) && $enrollment['completed'] ? 'true' : 'false'; ?>) {
                    markAsComplete();
                }
            });

            video.addEventListener('error', function(e) {
                console.error('Video error:', e);
                handleVideoError();
            });

            // Add volume and playback rate controls
            video.addEventListener('loadstart', function() {
                console.log('Video loading started');
            });

            video.addEventListener('canplay', function() {
                console.log('Video can start playing');
            });

            // Disable seeking via seeking event
            video.addEventListener('seeking', function() {
                if (video.currentTime > lastTime + 1) {
                    video.currentTime = lastTime;
                    showSkipWarning();
                }
            });
        }

        function togglePlayPause() {
            if (!video || !isVideoLoaded) {
                console.log('Video not ready');
                return;
            }
            
            try {
                if (video.paused) {
                    video.play().catch(e => {
                        console.error('Error playing video:', e);
                        alert('Unable to play video. Please check your internet connection.');
                    });
                } else {
                    video.pause();
                }
            } catch (error) {
                console.error('Error in togglePlayPause:', error);
            }
        }

        function updateProgress() {
            if (video && video.duration && isVideoLoaded) {
                let progress = (video.currentTime / video.duration) * 100;
                if (progressBar) {
                    progressBar.style.width = progress + '%';
                }
                updateTimeDisplay();
            }
        }

        function updateWatchTime() {
            if (video && video.duration && isVideoLoaded) {
                watchTimePercentage = Math.round((video.currentTime / video.duration) * 100);
                const watchTimeElement = document.getElementById('watchTime');
                if (watchTimeElement) {
                    watchTimeElement.textContent = watchTimePercentage + '%';
                }
                
                // Update course progress bar
                let courseProgressBar = document.getElementById('courseProgress');
                if (courseProgressBar && !<?php echo isset($enrollment['completed']) && $enrollment['completed'] ? 'true' : 'false'; ?>) {
                    courseProgressBar.style.width = watchTimePercentage + '%';
                }
                
                // Auto-complete at 95% watched (increased for unskippable video)
                if (watchTimePercentage >= 95 && !<?php echo isset($enrollment['completed']) && $enrollment['completed'] ? 'true' : 'false'; ?>) {
                    markAsComplete();
                }
            }
        }

        function updateTimeDisplay() {
            if (video && video.duration && timeDisplay && isVideoLoaded) {
                let current = formatTime(video.currentTime);
                let duration = formatTime(video.duration);
                timeDisplay.textContent = current + ' / ' + duration;
            }
        }

        function formatTime(seconds) {
            if (isNaN(seconds) || seconds < 0) return '0:00';
            let minutes = Math.floor(seconds / 60);
            let remainingSeconds = Math.floor(seconds % 60);
            return minutes + ':' + (remainingSeconds < 10 ? '0' : '') + remainingSeconds;
        }

        function setProgress(event) {
            // Disabled - Video is unskippable for educational integrity
            showSkipWarning();
            return;
        }

        function showSkipWarning() {
            // Create and show warning message
            let existingWarning = document.getElementById('skipWarning');
            if (existingWarning) {
                existingWarning.remove();
            }
            
            const warning = document.createElement('div');
            warning.id = 'skipWarning';
            warning.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(255, 107, 107, 0.95);
                color: white;
                padding: 1rem 2rem;
                border-radius: 10px;
                z-index: 1000;
                text-align: center;
                box-shadow: 0 5px 15px rgba(0,0,0,0.3);
                backdrop-filter: blur(10px);
            `;
            warning.innerHTML = `
                <i class="fas fa-exclamation-triangle" style="font-size: 1.5rem; margin-bottom: 0.5rem;"></i><br>
                <strong>Video cannot be skipped</strong><br>
                <small>Please watch the entire video to complete the course</small>
            `;
            
            document.body.appendChild(warning);
            
            setTimeout(() => {
                warning.remove();
            }, 3000);
        }

        function toggleFullscreen() {
            if (!video) return;
            
            try {
                if (video.requestFullscreen) {
                    video.requestFullscreen();
                } else if (video.webkitRequestFullscreen) {
                    video.webkitRequestFullscreen();
                } else if (video.msRequestFullscreen) {
                    video.msRequestFullscreen();
                }
            } catch (error) {
                console.error('Error entering fullscreen:', error);
            }
        }

        function markAsComplete() {
            const completionButton = document.querySelector('.mark-complete-btn');
            if (completionButton && completionButton.disabled) {
                return; // Already completed
            }
            
            fetch('mark_complete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'course_id=<?php echo $course_id; ?>'
            })
            .then(response => response.json())
            .then(data => {
                console.log('Course completion response:', data); // Debug logging
                if (data.success) {
                    // Update UI
                    const statusElement = document.getElementById('completionStatus');
                    if (statusElement) {
                        statusElement.textContent = 'Complete';
                    }
                    
                    const progressElement = document.getElementById('courseProgress');
                    if (progressElement) {
                        progressElement.style.width = '100%';
                    }
                    
                    const button = document.querySelector('.mark-complete-btn');
                    if (button) {
                        button.innerHTML = '<i class="fas fa-trophy"></i> Course Completed!';
                        button.disabled = true;
                    }
                    
                    // Show completion message with NFT certificate info
                    const completionMessage = document.getElementById('completionMessage');
                    if (completionMessage) {
                        completionMessage.classList.add('show');
                        
                        // Add NFT certificate information if awarded
                        if (data.certificate_awarded && data.nft_key) {
                            const certificateInfo = document.createElement('div');
                            certificateInfo.className = 'nft-certificate-info';
                            certificateInfo.innerHTML = `
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 10px; margin-top: 1rem; text-align: center;">
                                    <h3><i class="fas fa-certificate"></i> NFT Certificate Awarded!</h3>
                                    <p style="margin: 1rem 0;">${data.certificate_message}</p>
                                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                                        <strong>Your Unique NFT Key:</strong><br>
                                        <code style="background: rgba(0,0,0,0.3); padding: 0.5rem; border-radius: 4px; display: inline-block; margin-top: 0.5rem; word-break: break-all; font-size: 0.9rem;">${data.nft_key}</code>
                                    </div>
                                    <div style="background: rgba(255,255,255,0.2); padding: 1rem; border-radius: 8px; margin: 1rem 0;">
                                        <strong>Verification Code:</strong><br>
                                        <code style="background: rgba(0,0,0,0.3); padding: 0.5rem; border-radius: 4px; display: inline-block; margin-top: 0.5rem; font-size: 1.1rem; letter-spacing: 2px;">${data.verification_code}</code>
                                    </div>
                                    <div style="margin-top: 1rem;">
                                        <a href="my_certificates.php" style="background: white; color: #667eea; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: bold; margin-right: 1rem;">
                                            <i class="fas fa-certificate"></i> View My Certificates
                                        </a>
                                        <a href="verify_certificate.php?code=${data.verification_code}" target="_blank" style="background: rgba(255,255,255,0.2); color: white; padding: 0.75rem 1.5rem; border-radius: 8px; text-decoration: none; font-weight: bold;">
                                            <i class="fas fa-external-link-alt"></i> Verify Certificate
                                        </a>
                                    </div>
                                </div>
                            `;
                            completionMessage.appendChild(certificateInfo);
                        } else if (data.certificate_awarded === false && data.certificate_error) {
                            // Show certificate error message
                            const certificateError = document.createElement('div');
                            certificateError.className = 'nft-certificate-error';
                            certificateError.innerHTML = `
                                <div style="background: #e74c3c; color: white; padding: 1.5rem; border-radius: 10px; margin-top: 1rem; text-align: center;">
                                    <h3><i class="fas fa-exclamation-triangle"></i> Certificate Issue</h3>
                                    <p style="margin: 1rem 0;">${data.certificate_error}</p>
                                    <p style="font-size: 0.9rem; opacity: 0.8;">Don't worry! Your course completion has been recorded. You can contact support for assistance with the certificate.</p>
                                </div>
                            `;
                            completionMessage.appendChild(certificateError);
                        }
                        
                        completionMessage.scrollIntoView({ behavior: 'smooth' });
                    }
                    
                    // Add celebration effect
                    setTimeout(() => {
                        document.body.style.animation = 'celebration 1s ease-in-out';
                        
                        // Show fireworks effect for NFT certificate
                        if (data.certificate_awarded) {
                            showFireworks();
                        }
                    }, 500);
                } else {
                    alert('Error marking course as complete: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }

        // Keyboard shortcuts - DISABLED for unskippable video
        document.addEventListener('keydown', function(event) {
            if (video && isVideoLoaded && !event.target.matches('input, textarea')) {
                switch(event.code) {
                    case 'Space':
                        event.preventDefault();
                        togglePlayPause();
                        break;
                    case 'KeyF':
                        event.preventDefault();
                        toggleFullscreen();
                        break;
                    // Seeking shortcuts disabled for educational integrity
                    case 'ArrowLeft':
                    case 'ArrowRight':
                        event.preventDefault();
                        showSkipWarning();
                        break;
                    case 'ArrowUp':
                        event.preventDefault();
                        video.volume = Math.min(1, video.volume + 0.1);
                        break;
                    case 'ArrowDown':
                        event.preventDefault();
                        video.volume = Math.max(0, video.volume - 0.1);
                        break;
                }
            }
        });

        // Add celebration animation styles
        const style = document.createElement('style');
        style.textContent = `
            @keyframes celebration {
                0% { transform: scale(1); }
                50% { transform: scale(1.02); }
                100% { transform: scale(1); }
            }
            
            .video-placeholder {
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
            }
        `;
        document.head.appendChild(style);

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Course watching page loaded');
            if (video) {
                console.log('Video element found, waiting for load...');
            } else {
                console.log('No video element found on page');
            }
        });
    </script>
</body>
</html>