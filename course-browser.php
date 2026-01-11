<?php
session_start();
include 'db.php';

// Check user authentication and role
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $_SESSION['role'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;
$user_info = null;

if ($is_logged_in) {
    if ($user_role === 'learner') {
        // Get learner info
        $learner_query = "SELECT full_name FROM learners WHERE id = '$user_id'";
        $learner_result = mysqli_query($conn, $learner_query);
        $user_info = mysqli_fetch_assoc($learner_result);
    } elseif ($user_role === 'creator') {
        // Redirect creators to their course browser
        header('Location: course-browser-creator.php');
        exit();
    } elseif ($user_role === 'admin') {
        // Redirect admins to admin panel
        header('Location: admin.html');
        exit();
    }
}
// If not logged in, allow guest access to browse courses
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Courses - NFT Learning Platform</title>
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

        .page-header {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            text-align: center;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .page-header h1 {
            color: #f8f9fa;
            margin-bottom: 0.5rem;
            font-size: 2.5rem;
        }

        .page-header p {
            color: #d1d5db;
            font-size: 1.1rem;
        }

        .search-filters {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .search-bar {
            width: 100%;
            padding: 1rem;
            border: 2px solid rgba(220, 38, 38, 0.3);
            border-radius: 10px;
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            transition: border-color 0.3s;
            background: #2a2a2a;
            color: #e1e5e9;
        }

        .search-bar:focus {
            outline: none;
            border-color: #dc2626;
        }

        .search-bar::placeholder {
            color: #9ca3af;
        }

        .category-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .category-btn {
            padding: 0.8rem 1.5rem;
            border: 2px solid rgba(220, 38, 38, 0.3);
            background: #2a2a2a;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            color: #e1e5e9;
        }

        .category-btn:hover,
        .category-btn.active {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            border-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.4);
        }

        .courses-section {
            background: linear-gradient(135deg, #1f1f1f 0%, #2a2a2a 100%);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .section-title {
            color: #f8f9fa;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .course-card {
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #262626;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(220, 38, 38, 0.3);
            border-color: #dc2626;
        }

        .course-thumbnail {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            padding: 2rem;
            text-align: center;
            font-size: 3rem;
            position: relative;
        }

        .course-category-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .course-info {
            padding: 1.5rem;
        }

        .course-title {
            color: #f8f9fa;
            margin-bottom: 0.8rem;
            font-size: 1.3rem;
            line-height: 1.4;
        }

        .course-description {
            color: #d1d5db;
            margin-bottom: 1rem;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #d1d5db;
        }

        .course-price {
            font-weight: bold;
            color: #ef4444;
            font-size: 1.2rem;
        }

        .course-instructor {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #d1d5db;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s;
            flex: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.4);
        }

        .btn-secondary {
            background: #374151;
            color: #e1e5e9;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        .btn-secondary:hover {
            background: #4b5563;
            border-color: #dc2626;
        }

        .btn-enrolled {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            cursor: not-allowed;
        }

        .loading {
            text-align: center;
            padding: 3rem;
            color: #d1d5db;
        }

        .no-courses {
            text-align: center;
            padding: 3rem;
            color: #d1d5db;
        }

        .no-courses i {
            font-size: 4rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }

        .success-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            font-weight: bold;
            z-index: 1001;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            border: 1px solid rgba(5, 150, 105, 0.3);
        }

        .success-message.show {
            opacity: 1;
            transform: translateX(0);
        }

        .error-message {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            border: 1px solid rgba(220, 38, 38, 0.3);
        }

        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .category-filters {
                justify-content: center;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .course-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="logo">Learnity</div>
            <ul class="nav-links">
                <?php if ($is_logged_in && $user_role === 'learner'): ?>
                    <li><a href="home-learner.php">Home</a></li>
                    <li><a href="course-browser.php">Courses</a></li>
                    <li><a href="learner-profile.php">Profile</a></li>
                    <li><a href="my_certificates.php" class="nav-link">My Certificates</a></li>
                    <li><a href="nft-search.php">Search NFT</a></li>
                    <li><a href="login.html">Logout</a></li>
                <?php else: ?>
                    <li><a href="guest.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="course-browser.php"><i class="fas fa-book"></i> Browse Courses</a></li>
                    <li><a href="login.html"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                    <li><a href="register.html"><i class="fas fa-user-plus"></i> Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="page-header">
            <h1>Explore Courses</h1>
            <?php if ($is_logged_in && $user_role === 'learner'): ?>
                <p>Welcome back, <?php echo htmlspecialchars($user_info['full_name'] ?? 'Learner'); ?>! Discover amazing courses and continue your learning journey.</p>
            <?php else: ?>
                <p>Discover amazing courses created by expert instructors. <a href="login.html" style="color: #ef4444;">Login</a> or <a href="register.html" style="color: #ef4444;">Register</a> to enroll and start learning!</p>
            <?php endif; ?>
        </div>

        <div class="search-filters">
            <input 
                type="text" 
                id="searchBar" 
                class="search-bar" 
                placeholder="🔍 Search courses by title, description, or instructor..."
            >
            
            <div class="category-filters" id="categoryFilters">
                <button class="category-btn active" data-category="all">All Courses</button>
                <!-- Categories will be loaded dynamically -->
            </div>
        </div>

        <div class="courses-section">
            <h2 class="section-title" id="sectionTitle">All Courses</h2>
            
           
            <div class="loading" id="loadingIndicator" style="display: none;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #ef4444;"></i>
                <p>Loading courses...</p>
            </div>
            
            <div class="courses-grid" id="coursesGrid">
                <!-- Courses will be loaded dynamically -->
            </div>

            <div class="no-courses" id="noResults" style="display: none;">
                <i class="fas fa-search"></i>
                <h3>No courses found</h3>
                <p>Try adjusting your search criteria or browse different categories.</p>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    <div class="success-message" id="successMessage">
        Course enrolled successfully! 🎉
    </div>

    <script>
        // Pass PHP login status to JavaScript
        const userLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
        const userRole = <?php echo $user_role ? "'" . $user_role . "'" : 'null'; ?>;
        
        class CourseBrowser {
            constructor() {
                this.courses = [];
                this.enrolledCourses = [];
                this.filteredCourses = [];
                this.currentCategory = 'all';
                this.currentSearch = '';
                this.categories = [];
                this.isLoggedIn = userLoggedIn;
                this.userRole = userRole;
                this.init();
            }

            async init() {
                await this.loadCourses();
                // Only load enrolled courses if user is logged in as learner
                if (this.isLoggedIn && this.userRole === 'learner') {
                    await this.loadEnrolledCourses();
                }
                this.loadCategories();
                this.bindEvents();
                this.renderCourses();
            }

            bindEvents() {
                // Search functionality
                const searchBar = document.getElementById('searchBar');
                searchBar.addEventListener('input', (e) => {
                    this.currentSearch = e.target.value.toLowerCase().trim();
                    this.filterCourses();
                });
            }

            async loadCourses() {
                try {
                    const response = await fetch('get_all_courses.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.courses = data.courses;
                        this.filteredCourses = [...this.courses];
                    } else {
                        console.error('Error loading courses:', data.message);
                        this.showError(data.message);
                    }
                } catch (error) {
                    console.error('Network error:', error);
                    this.showError('Failed to load courses. Please try again.');
                }
            }

            async loadEnrolledCourses() {
                try {
                    const response = await fetch('get_enrolled_courses.php');
                    const data = await response.json();
                    
                    if (data.success) {
                        this.enrolledCourses = data.enrolled_courses.map(c => c.course_id);
                    }
                } catch (error) {
                    console.error('Error loading enrolled courses:', error);
                }
            }

            loadCategories() {
                // Extract unique categories from courses
                this.categories = [...new Set(this.courses.map(course => course.category))];
                this.renderCategoryFilters();
            }

            renderCategoryFilters() {
                const container = document.getElementById('categoryFilters');
                const allButton = container.querySelector('.category-btn[data-category="all"]');
                
                // Add event listener to the existing "All Courses" button
                if (allButton) {
                    console.log('Adding event listener to All Courses button');
                    allButton.addEventListener('click', (e) => this.handleCategoryClick(e));
                } else {
                    console.error('All Courses button not found!');
                }
                
                // Add category buttons
                this.categories.forEach(category => {
                    const button = document.createElement('button');
                    button.className = 'category-btn';
                    button.dataset.category = category;
                    button.textContent = category;
                    button.addEventListener('click', (e) => this.handleCategoryClick(e));
                    container.appendChild(button);
                });
                
                console.log('Category filters rendered. Total categories:', this.categories.length);
            }

            handleCategoryClick(e) {
                e.preventDefault();
                const selectedCategory = e.target.dataset.category;
                console.log('Category clicked:', selectedCategory);
                
                // Update active state
                document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
                e.target.classList.add('active');
                
                // Update current category and filter
                this.currentCategory = selectedCategory;
                console.log('Updated currentCategory to:', this.currentCategory);
                
                this.updateSectionTitle();
                this.filterCourses();
            }

            filterCourses() {
                console.log('Filtering courses. Category:', this.currentCategory, 'Search:', this.currentSearch);
                this.showLoading();
                
                setTimeout(() => {
                    this.filteredCourses = this.courses.filter(course => {
                        const matchesCategory = this.currentCategory === 'all' || course.category === this.currentCategory;
                        const matchesSearch = !this.currentSearch || 
                            course.title.toLowerCase().includes(this.currentSearch) ||
                            course.description.toLowerCase().includes(this.currentSearch) ||
                            course.creator_name.toLowerCase().includes(this.currentSearch);
                        
                        console.log(`Course: ${course.title}, Category: ${course.category}, Matches Category: ${matchesCategory}, Matches Search: ${matchesSearch}`);
                        return matchesCategory && matchesSearch;
                    });

                    console.log('Filtered courses count:', this.filteredCourses.length);
                    this.hideLoading();
                    this.renderCourses();
                }, 300);
            }

            renderCourses() {
                const container = document.getElementById('coursesGrid');
                const noResults = document.getElementById('noResults');

                if (this.filteredCourses.length === 0) {
                    container.style.display = 'none';
                    noResults.style.display = 'block';
                    return;
                }

                container.style.display = 'grid';
                noResults.style.display = 'none';

                container.innerHTML = this.filteredCourses.map(course => {
                    const isEnrolled = this.enrolledCourses.includes(parseInt(course.id));
                    return this.createCourseCard(course, isEnrolled);
                }).join('');

                this.bindEnrollButtons();
            }

            createCourseCard(course, isEnrolled) {
                // For guests, show login prompt instead of enroll button
                const enrollButton = this.isLoggedIn && this.userRole === 'learner' 
                    ? `<button 
                        class="btn ${isEnrolled ? 'btn-enrolled' : 'btn-primary'} enroll-btn" 
                        data-course-id="${course.id}"
                        ${isEnrolled ? 'disabled' : ''}
                    >
                        ${isEnrolled ? '✓ Enrolled' : '<i class="fas fa-plus"></i> Enroll Now'}
                    </button>`
                    : `<a href="login.html" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt"></i> Login to Enroll
                    </a>`;

                return `
                    <article class="course-card" data-course-id="${course.id}">
                        <div class="course-thumbnail">
                            ${course.thumbnail ? 
                                `<img src="${course.thumbnail}" alt="${this.escapeHtml(course.title)}" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">` : 
                                `<div style="width: 100%; height: 200px; background: linear-gradient(135deg, #dc2626, #991b1b); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem; border-radius: 8px;">📚</div>`
                            }
                            <span class="course-category-badge">${this.escapeHtml(course.category)}</span>
                        </div>
                        <div class="course-info">
                            <h3 class="course-title">${this.escapeHtml(course.title)}</h3>
                            <p class="course-description">${this.escapeHtml(course.description)}</p>
                            <div class="course-meta">
                                <div class="course-instructor">
                                    <i class="fas fa-user-tie"></i>
                                    ${this.escapeHtml(course.creator_name)}
                                </div>
                                <div class="course-price">
                                    ${parseFloat(course.price) === 0 ? 'Free' : '$' + parseFloat(course.price).toFixed(2)}
                                </div>
                            </div>
                            <div class="course-stats">
                                <span><i class="fas fa-clock"></i> ${course.duration} hours</span>
                                <span><i class="fas fa-users"></i> ${course.students_enrolled} students</span>
                                <span><i class="fas fa-star"></i> ${parseFloat(course.rating).toFixed(1)}</span>
                            </div>
                            <div class="course-actions">
                                <a href="course-info.php?id=${course.id}" class="btn btn-secondary">
                                    <i class="fas fa-info-circle"></i> View Details
                                </a>
                                ${enrollButton}
                            </div>
                        </div>
                    </article>
                `;
            }

            bindEnrollButtons() {
                // Only bind enroll buttons if user is logged in as learner
                if (this.isLoggedIn && this.userRole === 'learner') {
                    const enrollButtons = document.querySelectorAll('.enroll-btn:not([disabled])');
                    enrollButtons.forEach(btn => {
                        btn.addEventListener('click', (e) => {
                            const courseId = parseInt(e.target.dataset.courseId);
                            this.enrollInCourse(courseId, e.target);
                        });
                    });
                }
            }

            async enrollInCourse(courseId, button) {
                try {
                    button.disabled = true;
                    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enrolling...';

                    const response = await fetch('enroll_course.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ course_id: courseId })
                    });

                    const data = await response.json();

                    if (data.success) {
                        // Update local state
                        this.enrolledCourses.push(courseId);
                        
                        // Update button
                        button.innerHTML = '✓ Enrolled';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-enrolled');
                        
                        // Update course enrollment count
                        const course = this.courses.find(c => c.id == courseId);
                        if (course) {
                            course.students_enrolled = parseInt(course.students_enrolled) + 1;
                            this.renderCourses();
                        }

                        this.showSuccessMessage(data.message);
                    } else {
                        button.disabled = false;
                        button.innerHTML = '<i class="fas fa-plus"></i> Enroll Now';
                        this.showError(data.message);
                    }
                } catch (error) {
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-plus"></i> Enroll Now';
                    console.error('Enrollment error:', error);
                    this.showError('Failed to enroll. Please try again.');
                }
            }

            showSuccessMessage(message = 'Course enrolled successfully! 🎉') {
                const messageEl = document.getElementById('successMessage');
                messageEl.textContent = message;
                messageEl.classList.add('show');
                
                setTimeout(() => {
                    messageEl.classList.remove('show');
                }, 3000);
            }

            showError(message) {
                const container = document.getElementById('coursesGrid');
                container.innerHTML = `<div class="error-message">${message}</div>`;
            }

            showLoading() {
                document.getElementById('loadingIndicator').style.display = 'block';
                document.getElementById('coursesGrid').style.display = 'none';
                document.getElementById('noResults').style.display = 'none';
            }

            hideLoading() {
                document.getElementById('loadingIndicator').style.display = 'none';
            }

            updateSectionTitle() {
                const title = document.getElementById('sectionTitle');
                const newTitle = this.currentCategory === 'all' ? 'All Courses' : this.currentCategory + ' Courses';
                title.textContent = newTitle;
                console.log('Updated section title to:', newTitle);
            }

            escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text || '';
                return div.innerHTML;
            }
        }

        // Initialize the course browser
        document.addEventListener('DOMContentLoaded', () => {
            new CourseBrowser();
        });
    </script>
</body>
</html>
