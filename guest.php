
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Learnity - Blockchain Learning Platform</title>
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
            color: #e1e5e9;
            background: #1a1a1a;
            overflow-x: hidden;
        }

        /* Navigation Bar */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
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
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo:hover {
            color: #764ba2;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-link {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .nav-link:hover::after {
            width: 100%;
            left: 0;
        }

        .nav-link.cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
        }

        .nav-link.cta::after {
            display: none;
        }

        .nav-link.cta:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        /* Hamburger Menu */
        .hamburger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }

        .hamburger span {
            width: 25px;
            height: 3px;
            background: #333;
            transition: 0.3s;
        }

        /* Main Content */
        main {
            margin-top: 70px;
        }

        .section {
            padding: 80px 0;
            background: linear-gradient(135deg, #2d1b69 0%, #11998e 100%);
            color: #e1e5e9;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .section:nth-child(even) {
            background: linear-gradient(135deg, #1e1e1e 0%, #2d2d2d 100%);
            color: #e1e5e9;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }

        .content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            color: #f8f9fa;
        }

        .content h2 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            line-height: 1.2;
            color: #f8f9fa;
        }

        .content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            line-height: 1.8;
            opacity: 0.9;
            color: #d1d5db;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: rgba(255, 255, 255, 0.15);
            color: #e1e5e9;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .section:nth-child(even) .cta-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: 2px solid transparent;
            color: white;
        }

        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.25);
        }

        .section:nth-child(even) .cta-button:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .placeholder-image {
            font-size: 10rem;
            opacity: 0.8;
            text-align: center;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        /* Features Grid */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 3rem;
            grid-column: 1 / -1;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.05);
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
        }

        .section:nth-child(even) .feature-card {
            background: rgba(255, 255, 255, 0.03);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .feature-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #f8f9fa;
        }

        .feature-card p {
            opacity: 0.8;
            line-height: 1.6;
            color: #d1d5db;
        }

        /* Footer */
        footer {
            background: #0f0f0f;
            color: #e1e5e9;
            text-align: center;
            padding: 2rem 0;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hamburger {
                display: flex;
            }

            .nav-menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                flex-direction: column;
                padding: 1rem 0;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            }

            .nav-menu.active {
                display: flex;
            }

            .container {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .content h1 {
                font-size: 2.5rem;
            }

            .content h2 {
                font-size: 2rem;
            }

            .placeholder-image {
                font-size: 6rem;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="nav-container">
            <a href="guest.php" class="logo"><i class="fas fa-graduation-cap"></i> Learnity</a>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="guest.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="course-browser-guest.php" class="nav-link"><i class="fas fa-book"></i> Courses</a></li>
                <li><a href="nft-search-guest.php" class="nav-link"><i class="fas fa-search"></i> Search NFT</a></li>
                <li><a href="login.html" class="nav-link cta"><i class="fas fa-sign-in-alt"></i> Login/Register</a></li>
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
        <section class="section" id="home">
            <div class="container">
                <div class="content">
                    <h1>Transform Learning with Blockchain Technology</h1>
                    <p>Join Learnity, the revolutionary platform where education meets blockchain. Create courses, earn NFT certificates, and build your reputation in the decentralized learning ecosystem.</p>
                    <a href="login.html" class="cta-button"><i class="fas fa-rocket"></i> Start Your Learning Journey</a>
                </div>
                <div class="image-container">
                    <div class="placeholder-image">🎓</div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="section" id="courses">
            <div class="container">
                <div class="image-container">
                    <div class="placeholder-image">📚</div>
                </div>
                <div class="content">
                    <h2>Discover Unlimited Learning Opportunities</h2>
                    <p>Explore courses created by expert instructors from around the world. From programming to design, business to personal development - find your passion and master new skills.</p>
                    <a href="course-browser-guest.php" class="cta-button"><i class="fas fa-search"></i> Browse Courses</a>
                </div>
            </div>
        </section>

        <!-- NFT Certificates Section -->
        <section class="section" id="certificates">
            <div class="container">
                <div class="content">
                    <h2>Earn Verified NFT Certificates</h2>
                    <p>Complete courses and receive blockchain-verified NFT certificates that prove your achievements. These certificates are permanently stored on the blockchain and can be verified anywhere, anytime.</p>
                    <a href="nft-search-guest.php" class="cta-button"><i class="fas fa-certificate"></i> Verify Certificates</a>
                </div>
                <div class="image-container">
                    <div class="placeholder-image">🏆</div>
                </div>
            </div>
        </section>

        <!-- Creator Section -->
        <section class="section" id="create">
            <div class="container">
                <div class="image-container">
                    <div class="placeholder-image">👨‍🏫</div>
                </div>
                <div class="content">
                    <h2>Share Your Knowledge</h2>
                    <p>Become a course creator and share your expertise with learners worldwide. Create engaging content, build your audience, and earn from your knowledge while helping others grow.</p>
                    <a href="register.html" class="cta-button"><i class="fas fa-plus-circle"></i> Start Teaching</a>
                </div>
            </div>
        </section>

        <!-- Features Grid -->
        <section class="section" id="features">
            <div class="container">
                <div class="content" style="text-align: center; grid-column: 1 / -1;">
                    <h2>Why Choose Learnity?</h2>
                    <p>Experience the future of learning with blockchain technology</p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Blockchain Security</h3>
                        <p>All certificates are secured on the blockchain, ensuring authenticity and preventing fraud.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🌍</div>
                        <h3>Global Recognition</h3>
                        <p>Your achievements are recognized worldwide with verifiable NFT certificates.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Instant Verification</h3>
                        <p>Verify any certificate instantly using our blockchain-powered verification system.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">🎯</div>
                        <h3>Personalized Learning</h3>
                        <p>AI-powered recommendations help you find courses that match your interests and goals.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">💰</div>
                        <h3>Earn While Learning</h3>
                        <p>Creators earn from their courses while learners gain valuable skills and certificates.</p>
                    </div>
                    
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Mobile Friendly</h3>
                        <p>Learn anywhere, anytime with our responsive platform that works on all devices.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <p>&copy; <?php echo date('Y'); ?> Learnity. All rights reserved. | Powered by Blockchain Technology</p>
    </footer>

    <script>
        // Set current year
        document.getElementById('currentYear')?.textContent = new Date().getFullYear();

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        hamburger.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href').startsWith('#')) {
                    e.preventDefault();
                    const target = document.querySelector(link.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });

        // Navbar background change on scroll
        window.addEventListener('scroll', () => {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
            }
        });

        // Animate elements on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe feature cards
        document.querySelectorAll('.feature-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
