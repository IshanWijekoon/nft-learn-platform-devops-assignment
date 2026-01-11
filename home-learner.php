<?php
session_start();
include 'db.php';

// Check if user is logged in as learner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'learner') {
    header('Location: login.html');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['full_name'] ?? 'Learner';

// Get learner info
$learner_query = "SELECT full_name FROM learners WHERE id = '$user_id'";
$learner_result = mysqli_query($conn, $learner_query);
$learner = mysqli_fetch_assoc($learner_result);
if ($learner) {
    $user_name = $learner['full_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learner Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #e1e5e9;
            background: #1a1a1a;
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
            gap: 30px;
            align-items: center;
        }

        .nav-menu li {
            position: relative;
        }

        .nav-menu a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            font-size: 1rem;
            padding: 10px 16px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
            overflow: hidden;
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

        .hamburger.active span:nth-child(1) {
            transform: rotate(-45deg) translate(-5px, 6px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(45deg) translate(-5px, -6px);
        }

        /* Main Content */
        main {
            margin-top: 70px;
            min-height: calc(100vh - 140px);
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: #f8f9fa;
            padding: 80px 20px;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero p {
            font-size: 1.3rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 15px 35px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 20px rgba(239, 68, 68, 0.4);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(239, 68, 68, 0.6);
            background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
        }

        /* Categories Section */
        .categories {
            padding: 80px 20px;
            background: #2d2d2d;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: #f8f9fa;
            margin-bottom: 50px;
        }

        .categories-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
        }

        .category-card {
            background: #1f1f1f;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 2px solid rgba(220, 38, 38, 0.2);
        }

        .category-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(220, 38, 38, 0.3);
            border-color: #dc2626;
            background: #262626;
        }

        .category-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #ef4444;
        }

        .category-card h3 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #f8f9fa;
        }

        .category-card p {
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .explore-btn {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .explore-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 38, 38, 0.5);
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        /* Popular Courses Section */
        .popular-courses {
            padding: 80px 20px;
            background: #1a1a1a;
        }

        .courses-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }

        .course-card {
            background: #1f1f1f;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 1px solid rgba(220, 38, 38, 0.2);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(220, 38, 38, 0.3);
            border-color: #dc2626;
        }

        .course-image {
            height: 200px;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #f8f9fa;
        }

        .course-content {
            padding: 25px;
        }

        .course-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #f8f9fa;
        }

        .course-instructor {
            color: #d1d5db;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .course-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }

        .stars {
            color: #fbbf24;
        }

        .rating-number {
            color: #d1d5db;
            font-size: 0.9rem;
        }

        .course-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #ef4444;
        }

        /* Footer */
        footer {
            background: #0f0f0f;
            color: #e1e5e9;
            text-align: center;
            padding: 40px 20px;
            border-top: 2px solid #dc2626;
        }

        footer p {
            margin-bottom: 20px;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .footer-links a {
            color: rgba(225, 229, 233, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #ef4444;
        }

        /* Responsive Design */
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
                gap: 20px;
                padding-top: 50px;
                transition: left 0.3s ease;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-menu a {
                width: 80%;
                text-align: center;
                padding: 15px 20px;
                font-size: 1.1rem;
                margin: 5px 0;
            }

            .nav-menu a[href="login.html"] {
                margin-left: 0;
                margin-top: 20px;
            }

            .hamburger {
                display: flex;
            }

            .hero h1 {
                font-size: 2.2rem;
            }

            .hero p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .categories-grid,
            .courses-grid {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 60px 15px;
            }

            .hero h1 {
                font-size: 1.8rem;
            }

            .categories,
            .popular-courses {
                padding: 60px 15px;
            }

            .category-card,
            .course-content {
                padding: 20px;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">Learnity</a>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="home-learner.php">Home</a></li>
                    <li><a href="course-browser.php">Courses</a></li>
                    <li><a href="learner-profile.php">Profile</a></li>
                    <li><a href="my_certificates.php" class="nav-link">My Certificates</a></li>
                    <li><a href="nft-search.php">Search NFT</a></li>
                    <li><a href="login.html">Logout</a></li>
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
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-content">
                <h1>Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                <p>Continue your learning journey with thousands of courses, earn blockchain certificates, and advance your career with Learnity's innovative learning platform.</p>
                <a href="course-browser.php" class="cta-button">Browse Courses</a>
            </div>
        </section>

        <!-- Categories Section -->
        <section class="categories" id="categories">
            <h2 class="section-title">Explore Categories</h2>
            <div class="categories-grid">
                <div class="category-card">
                    <div class="category-icon">💻</div>
                    <h3>Web Development</h3>
                    <p>Learn HTML, CSS, JavaScript, React, and more to build modern websites and applications.</p>
                    <button class="explore-btn" onclick="window.location.href='course-browser.php'">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">📱</div>
                    <h3>Mobile Development</h3>
                    <p>Create iOS and Android apps using React Native, Flutter, Swift, and Kotlin.</p>
                    <button class="explore-btn" onclick="window.location.href='course-browser.php'">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">🔗</div>
                    <h3>Blockchain</h3>
                    <p>Master cryptocurrency, smart contracts, DeFi, and blockchain development.</p>
                    <button class="explore-btn" onclick="window.location.href='course-browser.php'">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">📊</div>
                    <h3>Data Science</h3>
                    <p>Analyze data with Python, R, machine learning, and artificial intelligence.</p>
                    <button class="explore-btn" onclick="window.location.href='course-browser.php'">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">🎨</div>
                    <h3>Design</h3>
                    <p>Learn UI/UX design, graphic design, and digital marketing strategies.</p>
                    <button class="explore-btn" onclick="window.location.href='course-browser.php'">Explore</button>
                </div>
                
                <div class="category-card">
                    <div class="category-icon">💼</div>
                    <h3>Business</h3>
                    <p>Develop entrepreneurship, management, and leadership skills for success.</p>
                    <button class="explore-btn" onclick="window.location.href='course-browser.php'">Explore</button>
                </div>
            </div>
        </section>

        <!-- Popular Courses Section -->
       
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <p>&copy; <span id="currentYear"></span> EduChain. All rights reserved.</p>
    </footer>

    <script>
        // Set current year
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        // Close mobile menu when clicking on a link
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            });
        });

        // Set active state for current page
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-menu a');
            
            navLinks.forEach(link => {
                const linkPath = new URL(link.href).pathname;
                if (currentPath.includes(linkPath.split('/').pop()) && linkPath !== '/login.html') {
                    link.classList.add('active');
                }
            });
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 70;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 10) {
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>