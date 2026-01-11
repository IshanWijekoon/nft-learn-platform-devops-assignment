<?php
session_start();
include 'db.php';

// Handle search functionality
$search_message = '';
$search_error = '';
$nft_key = '';

if ($_POST && isset($_POST['nft_key'])) {
    $nft_key = trim($_POST['nft_key']);
    
    if (!empty($nft_key)) {
        // Search for NFT certificate by NFT key
        $search_query = "
            SELECT nc.*, nv.verification_code, c.course_name, l.full_name as learner_name
            FROM nft_certificates nc
            LEFT JOIN nft_verifications nv ON nc.id = nv.certificate_id
            LEFT JOIN courses c ON nc.course_id = c.id
            LEFT JOIN learners l ON nc.learner_id = l.id
            WHERE nc.nft_key = ?
        ";
        
        $stmt = $conn->prepare($search_query);
        $stmt->bind_param("s", $nft_key);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $certificate = $result->fetch_assoc();
            // Redirect to verify_certificate.php with the verification code
            header("Location: verify_certificate.php?code=" . $certificate['verification_code']);
            exit();
        } else {
            $search_error = "No NFT certificate found for this key. Please verify the NFT key and try again.";
        }
    } else {
        $search_error = "Please enter an NFT key to search.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFT Certificate Search - Learnity</title>
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
            color: #e0e0e0;
            background: linear-gradient(135deg, #121212 0%, #1a1a1a 100%);
            min-height: 100vh;
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
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
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

       
        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .logo:hover {
            color: #667eea;
        }

        .nav-menu a[href="login.html"] {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white !important;
            font-weight: 600;
            margin-left: 10px;
        }

        .nav-menu a[href="login.html"]:hover {
            background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }

        .nav-menu a[href="login.html"]::before {
            display: none;
        }

        /* Main Content */
        .main-container {
            margin-top: 70px;
            padding: 2rem 1rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 2rem;
            background: rgba(30, 30, 30, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .page-header h1 {
            font-size: 2.5rem;
            color: #ffffff;
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .page-header p {
            font-size: 1.1rem;
            color: #b0b0b0;
            line-height: 1.6;
        }

        /* Search Section */
        .search-section {
            background: rgba(30, 30, 30, 0.95);
            border-radius: 20px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .search-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .search-group label {
            font-weight: 600;
            color: #ffffff;
            font-size: 1.1rem;
        }

        .search-input {
            padding: 1rem 1.5rem;
            font-size: 1rem;
            border: 2px solid #444444;
            border-radius: 12px;
            background: #2a2a2a;
            color: #e0e0e0;
            transition: all 0.3s ease;
            font-family: 'Courier New', monospace;
        }

        .search-input:focus {
            outline: none;
            border-color: #4a90e2;
            box-shadow: 0 0 0 4px rgba(74, 144, 226, 0.2);
            transform: translateY(-2px);
        }

        .search-input::placeholder {
            color: #888888;
        }

        .search-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .search-btn,
        .reset-btn {
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
            min-width: 150px;
        }

        .search-btn {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
        }

        .search-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(74, 144, 226, 0.4);
        }

        .reset-btn {
            background: #2a2a2a;
            color: #b0b0b0;
            border: 2px solid #444444;
        }

        .reset-btn:hover {
            background: #3a3a3a;
            color: #ffffff;
            transform: translateY(-2px);
        }

        /* Alert Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-error {
            background: #b71c1c;
            color: #ffcdd2;
            border: 1px solid #f44336;
        }

        .alert-success {
            background: #1b5e20;
            color: #a5d6a7;
            border: 1px solid #4caf50;
        }

        /* Instructions */
        .instructions {
            background: rgba(30, 30, 30, 0.95);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
        }

        .instructions h3 {
            color: #ffffff;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        .instructions ul {
            list-style: none;
            padding: 0;
        }

        .instructions li {
            padding: 0.5rem 0;
            color: #b0b0b0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .instructions li::before {
            content: "✓";
            color: #4a90e2;
            font-weight: bold;
        }

        /* Mobile Navigation */
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
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

            .page-header h1 {
                font-size: 2rem;
            }

            .search-section {
                padding: 1.5rem;
            }

            .search-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">Learnity</a>
            
            <ul class="nav-menu" id="navMenu">
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'learner'): ?>
                   <li><a href="home-learner.php">Home</a></li>
                    <li><a href="course-browser.php">Courses</a></li>
                    <li><a href="learner-profile.php">Profile</a></li>
                    <li><a href="my_certificates.php">My Certificates</a></li>
                    <li><a href="nft-search.php" class="active">Search NFT</a></li>
                    <li><a href="login.html">Logout</a></li>
                <?php elseif (isset($_SESSION['user_id']) && $_SESSION['role'] === 'creator'): ?>
                    <li><a href="home-creator.php">Home</a></li>
                    <li><a href="course-browser-creator.php">Courses</a></li>
                    <li><a href="course-management.php">Course Management</a></li>
                    <li><a href="creator-profile.php">Profile</a></li>
                    <li><a href="nft-search.php" class="active">Search NFT</a></li>
                    <li><a href="login.html">Logout</a></li>
                <?php else: ?>
                    <li><a href="guest.php">Home</a></li>
                    <li><a href="nft-search.php" class="active">Search NFT</a></li>
                    <li><a href="login.html">Login</a></li>
                    <li><a href="register.html">Register</a></li>
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
    <main class="main-container">
        <!-- Page Header -->
        <header class="page-header">
            <h1>
                <i class="fas fa-search"></i> NFT Certificate Search
            </h1>
            <p>
                Verify and search for blockchain-verified educational certificates. 
                Enter an NFT Key to view detailed certificate information and verification status.
            </p>
        </header>

        <!-- Alert Messages -->
        <?php if (!empty($search_error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($search_error); ?>
            </div>
        <?php endif; ?>

        <!-- Search Section -->
        <section class="search-section">
            <form class="search-form" method="POST">
                <div class="search-group">
                    <label for="nft_key"><i class="fas fa-key"></i> Enter NFT Key</label>
                    <input 
                        type="text" 
                        id="nft_key" 
                        name="nft_key"
                        class="search-input" 
                        placeholder="e.g., NFT936A6064183ACDA7A64C47E7060FAA0E1757365260"
                        value="<?php echo htmlspecialchars($nft_key); ?>"
                        required
                        autocomplete="off">
                </div>

                <div class="search-buttons">
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search Certificate
                    </button>
                    <button type="button" class="reset-btn" onclick="document.getElementById('nft_key').value=''; window.location.href='nft-search.php';">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </form>
        </section>

        <!-- Instructions -->
        <section class="instructions">
            <h3><i class="fas fa-info-circle"></i> How to Search</h3>
            <ul>
                <li>Enter the complete NFT Key in the search box above</li>
                <li>NFT Keys are long alphanumeric strings (e.g., NFT936A6064183ACDA7A64C47E7060FAA0E1757365260)</li>
                <li>If the certificate exists, you'll be redirected to the verification page</li>
                <li>The verification page will show who earned the certificate and when</li>
                <li>All searches are logged for security purposes</li>
            </ul>
        </section>
    </main>

    <script>
        // Auto-focus on the search input
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('nft_key').focus();
        });

        // Format NFT key input (optional - remove spaces and convert to uppercase)
        document.getElementById('nft_key').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '').toUpperCase();
            e.target.value = value;
        });

        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const navMenu = document.getElementById('navMenu');

        if (hamburger && navMenu) {
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

            // Close mobile menu when clicking outside
            document.addEventListener('click', (e) => {
                if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                    hamburger.classList.remove('active');
                    navMenu.classList.remove('active');
                }
            });
        }

        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 10) {
                navbar.style.boxShadow = '0 2px 30px rgba(0, 0, 0, 0.2)';
            } else {
                navbar.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            }
        });
    </script>
</body>
</html>
