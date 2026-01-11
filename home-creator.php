<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduChain - Become an Instructor</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #e0e0e0;
            background: #121212;
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background                    <a href="course-management.php" class="cta-btn-primary">Start Teaching Today</a> white;
            box-shadow: 0 2px 15px rgba(255, 255, 255, 0.1);
            z-index: 1000;
            background: #ffffffff;
            padding: 0 20px;
            transition: all 0.3s ease;
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
            color: black;
        }


        .nav-menu {
            display: flex;
            list-style: none;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            position: relative;
            transition: color 0.3s ease;
            padding: 8px 0;
        }

        .nav-link:hover {
            color: #2c5aa0;
        }

        .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #2c5aa0;
            transition: width 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
        }

        .logout-btn {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white !important;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            color: white !important;
        }

        .logout-btn::after {
            display: none;
        }

        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            padding: 5px;
            background: none;
            border: none;
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
        }

        /* Hero Section */
        .hero {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            padding: 80px 20px;
            min-height: 90vh;
            display: flex;
            align-items: center;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-content p {
            font-size: 1.3rem;
            margin-bottom: 40px;
            opacity: 0.9;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 18px 40px;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.3);
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 107, 53, 0.4);
            background: linear-gradient(135deg, #f7931e 0%, #ff6b35 100%);
        }

        .hero-image {
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .hero-illustration {
            width: 100%;
            max-width: 500px;
            height: 400px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .hero-illustration:hover {
            transform: scale(1.05);
        }

        /* Features Section */
        .features {
            padding: 100px 20px;
            background: #1e1e1e;
        }

        .features-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            text-align: center;
            font-size: 2.8rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 20px;
        }

        .section-subtitle {
            text-align: center;
            font-size: 1.2rem;
            color: #b0b0b0;
            margin-bottom: 60px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 40px;
        }

        .feature-card {
            background: #2a2a2a;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
        }

        .feature-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            border-color: #4a90e2;
        }

        .feature-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            transition: transform 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-card h3 {
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .feature-card p {
            color: #b0b0b0;
            line-height: 1.7;
            margin-bottom: 25px;
        }

        .feature-button {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .feature-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        }

        /* Call to Action Section */
        .cta-section {
            background: linear-gradient(135deg, #0f0f23 0%, #1a1a2e 100%);
            color: white;
            padding: 80px 20px;
            text-align: center;
        }

        .cta-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .cta-section h2 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .cta-section p {
            font-size: 1.2rem;
            margin-bottom: 40px;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .cta-btn-primary {
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            padding: 15px 35px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-btn-secondary {
            background: transparent;
            color: white;
            padding: 15px 35px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .cta-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(255, 107, 53, 0.4);
        }

        .cta-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: white;
            transform: translateY(-3px);
        }

        /* Footer */
        footer {
            background: #0a0a0a;
            color: white;
            padding: 50px 20px 30px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 40px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            color: rgba(255, 255, 255, 0.7);
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

            .hero-container {
                grid-template-columns: 1fr;
                gap: 40px;
                text-align: center;
            }

            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .hero-illustration {
                height: 300px;
                font-size: 4rem;
            }

            .section-title {
                font-size: 2.2rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .feature-card {
                padding: 30px;
            }

            .cta-section h2 {
                font-size: 2rem;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }

            .footer-links {
                flex-direction: column;
                gap: 20px;
            }
        }

        @media (max-width: 480px) {
            .hero {
                padding: 60px 15px;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .features,
            .cta-section {
                padding: 60px 15px;
            }

            .feature-card {
                padding: 25px;
            }

            .section-title {
                font-size: 1.8rem;
            }

            .nav-container {
                padding: 0 15px;
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

        /* Loading animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .feature-card {
            animation: fadeInUp 0.6s ease-out;
        }

        .feature-card:nth-child(1) { animation-delay: 0.1s; }
        .feature-card:nth-child(2) { animation-delay: 0.2s; }
        .feature-card:nth-child(3) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
              <a href="#" class="logo">Learnity</a>
            
            <ul class="nav-menu" id="navMenu">
                 <li><a href="home-creater.html" class="nav-link">Home</a></li>
                <li><a href="course-browser-creator.php" class="nav-link">Courses</a></li>
                <li><a href="course-management.php" class="nav-link">Course Management</a></li>
                <li><a href="creator-profile.php" class="nav-link">Profile</a></li>
                <li><a href="nft-search.php" class="nav-link">Search NFT</a></li>
                <li><a href="login.html" class="nav-link logout-btn">Logout</a></li>
            </ul>

            <button class="hamburger" id="hamburger" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-container">
                <div class="hero-content">
                    <h1>Share Your Knowledge, Shape the Future</h1>
                    <p>Join thousands of instructors creating impactful courses on EduChain. Build your reputation, earn blockchain-verified credentials, and monetize your expertise in our innovative learning ecosystem.</p>
                    <a href="course-management.php" class="cta-button">Create Your First Course</a>
                </div>
                <div class="hero-image">
                    <div class="hero-illustration">🎓</div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <div class="features-container">
                <h2 class="section-title">Empower Your Teaching Journey</h2>
                <p class="section-subtitle">Everything you need to create, manage, and grow your online teaching business with blockchain-powered innovation.</p>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📚</div>
                        <h3>Manage Your Courses</h3>
                        <p>Create engaging content with our intuitive course builder. Upload videos, documents, and interactive materials. Track student progress and engagement with detailed analytics.</p>
                        <a href="#" class="feature-button" onclick="alert('Course management functionality would open here')">Get Started</a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Track Your Earnings & Students</h3>
                        <p>Monitor your revenue growth, student enrollments, and course performance with comprehensive dashboards. Get insights to optimize your teaching strategy and maximize earnings.</p>
                        <a href="#" class="feature-button" onclick="alert('Analytics dashboard would open here')">View Analytics</a>
                    </div>

                    <div class="feature-card">
                        <div class="feature-icon">🏆</div>
                        <h3>Get Certified with NFT Badges</h3>
                        <p>Issue blockchain-verified NFT certificates to your students upon course completion. These tamper-proof credentials enhance your courses' value and students' career prospects.</p>
                        <a href="#" class="feature-button" onclick="alert('NFT certification info would open here')">Learn More</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Section -->
        <section class="cta-section">
            <div class="cta-container">
                <h2>Ready to Transform Lives Through Teaching?</h2>
                <p>Join our community of passionate educators and start building your teaching empire today. Your expertise can change the world, one student at a time.</p>
                <div class="cta-buttons">
                    <a href="course-management.php" class="cta-btn-primary">Start Teaching Today</a>
                    <a href="#" class="cta-btn-secondary" onclick="alert('Instructor guide would open here')">Learn How It Works</a>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-links">
                <a href="#">About Us</a>
                <a href="#">Contact</a>
                <a href="#">Help Center</a>
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
                <a href="#">Instructor Guidelines</a>
            </div>
            <div class="footer-bottom">
                <p>&copy; <span id="currentYear"></span> EduChain. All rights reserved. Empowering education through blockchain technology.</p>
            </div>
        </div>
    </footer>

    <script>
        // Set current year in footer
        document.getElementById('currentYear').textContent = new Date().getFullYear();

        // Mobile menu toggle
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
                navbar.style.boxShadow = '0 2px 25px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        });

        // Enhanced button interactions
        document.querySelectorAll('.cta-button, .feature-button, .cta-btn-primary, .cta-btn-secondary').forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, observerOptions);

        // Observe feature cards for animations
        document.querySelectorAll('.feature-card').forEach(card => {
            observer.observe(card);
        });

        // Add keyboard navigation support
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hamburger.classList.remove('active');
                navMenu.classList.remove('active');
            }
        });

        // Performance optimization: Debounce scroll events
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Apply debounced scroll handler
        const debouncedScrollHandler = debounce(() => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 10) {
                navbar.style.boxShadow = '0 2px 25px rgba(0, 0, 0, 0.15)';
            } else {
                navbar.style.boxShadow = '0 2px 15px rgba(0, 0, 0, 0.1)';
            }
        }, 10);

        window.addEventListener('scroll', debouncedScrollHandler);
    </script>
</body>
</html>