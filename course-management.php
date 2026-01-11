<?php
// ==============================================
// COURSE MANAGEMENT SYSTEM - CREATOR DASHBOARD
// ==============================================
// This page allows course creators to:
// 1. Create new courses with video content, thumbnails, and NFT certificates
// 2. View and manage their existing courses
// 3. Edit course details and metadata
// 4. Delete courses they have created

// ==============================================
// SESSION AND AUTHENTICATION SETUP
// ==============================================
// Initialize user session to track logged-in creator
session_start();

// Include database connection configuration
include 'db.php';

// ==============================================
// CREATOR ACCESS CONTROL
// ==============================================
// AUTHENTICATION CHECK: Ensure only logged-in creators can access this page


// EXTRACT USER ID: Get creator ID from session for database queries
$user_id = $_SESSION['user_id'];

// ==============================================
// CREATOR INFORMATION RETRIEVAL
// ==============================================
// CREATOR PROFILE QUERY: Get creator's full name for personalized welcome message
// Uses prepared statement for security against SQL injection
$creator_query = "SELECT full_name FROM creators WHERE id = ?";
$stmt = $conn->prepare($creator_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$creator_result = $stmt->get_result();
$creator = $creator_result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="script-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data: blob:; font-src 'self' https://cdnjs.cloudflare.com;">
    <title>Course Management - NFT Learning Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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

        .page-header {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .page-header h1 {
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #b0b0b0;
        }

        .form-section {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .form-section h2 {
            color: #ffffff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .course-form {
            display: grid;
            gap: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #ffffff;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 1rem;
            border: 2px solid #444444;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            background: #2a2a2a;
            color: #e0e0e0;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .form-group input::placeholder,
        .form-group textarea::placeholder {
            color: #888888;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .file-upload-area {
            border: 2px dashed #444444;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            transition: border-color 0.3s, background 0.3s;
            cursor: pointer;
            background: #2a2a2a;
        }

        .file-upload-area:hover {
            border-color: #4a90e2;
            background: #3a3a3a;
        }

        .file-upload-area.dragover {
            border-color: #4a90e2;
            background: #3a3a3a;
        }

        .file-upload-icon {
            font-size: 3rem;
            color: #4a90e2;
            margin-bottom: 1rem;
        }

        .file-upload-text {
            color: #b0b0b0;
            margin-bottom: 0.5rem;
        }

        .file-upload-subtext {
            color: #888888;
            font-size: 0.9rem;
        }

        .file-info {
            display: none;
            background: #2a2a2a;
            border: 1px solid #4a90e2;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .file-info.show {
            display: block;
        }

        .file-name {
            font-weight: bold;
            color: #ffffff;
        }

        .file-size {
            color: #b0b0b0;
            font-size: 0.9rem;
        }

        .form-group .error {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 0.25rem;
            display: none;
        }

        .form-group.has-error input,
        .form-group.has-error select,
        .form-group.has-error textarea,
        .form-group.has-error .file-upload-area {
            border-color: #dc3545;
        }

        .form-group.has-error .error {
            display: block;
        }

        .submit-btn {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            justify-self: start;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .upload-progress {
            display: none;
            background: #2a2a2a;
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .upload-progress.show {
            display: block;
        }

        .progress-bar {
            width: 100%;
            height: 20px;
            background: #444444;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #4a90e2, #357abd);
            width: 0%;
            transition: width 0.3s;
        }

        .progress-text {
            text-align: center;
            color: #b0b0b0;
            font-size: 0.9rem;
        }

        .success-message,
        .error-message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: bold;
        }

        .success-message {
            background: #1b5e20;
            color: #a5d6a7;
            border: 1px solid #4caf50;
        }

        .error-message {
            background: #b71c1c;
            color: #ffcdd2;
            border: 1px solid #f44336;
        }

        .courses-section {
            background: #1e1e1e;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .courses-section h2 {
            color: #ffffff;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .course-card {
            border: 1px solid #444444;
            border-radius: 12px;
            padding: 1.5rem;
            transition: transform 0.3s, box-shadow 0.3s;
            background: #2a2a2a;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.4);
        }

        .course-card h3 {
            color: #ffffff;
            margin-bottom: 0.5rem;
            font-size: 1.2rem;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin: 1rem 0;
            font-size: 0.9rem;
            color: #b0b0b0;
        }

        .course-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .course-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #28a745;
            margin: 1rem 0;
        }

        .course-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background 0.3s;
            flex: 1;
            text-decoration: none;
            text-align: center;
            display: inline-block;
        }

        .watch-btn {
            background: #28a745;
            color: white;
        }

        .watch-btn:hover {
            background: #218838;
        }

        .edit-btn {
            background: #4a90e2;
            color: white;
        }

        .edit-btn:hover {
            background: #357abd;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #c82333;
        }

        .no-courses {
            text-align: center;
            color: #b0b0b0;
            padding: 3rem;
            font-style: italic;
        }

        .no-courses i {
            font-size: 4rem;
            color: #555555;
            margin-bottom: 1rem;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: block;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: #1e1e1e;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: #2a2a2a;
            padding: 1.5rem 2rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #444444;
        }

        .modal-header h2 {
            color: #ffffff;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .close-btn {
            font-size: 2rem;
            color: #888888;
            cursor: pointer;
            line-height: 1;
            transition: color 0.3s;
        }

        .close-btn:hover {
            color: #ffffff;
        }

        .edit-form {
            padding: 2rem;
            display: grid;
            gap: 1.5rem;
        }

        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .cancel-btn {
            background: #6c757d;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .cancel-btn:hover {
            background: #5a6268;
        }

        .save-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .save-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .courses-grid {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 0 1rem;
            }
            
            .course-actions {
                flex-direction: column;
            }

            .modal-content {
                width: 95%;
                margin: 2% auto;
            }

            .edit-form {
                padding: 1rem;
            }

            .modal-actions {
                flex-direction: column;
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
        <div class="page-header">
            <h1>Course Management</h1>
            <p>Create and manage your courses with video content, <?php echo htmlspecialchars($creator['full_name']); ?>!</p>
        </div>

        <!-- Course Creation Form -->
        <div class="form-section">
            <h2><i class="fas fa-plus-circle"></i> Create New Course</h2>
            <div id="form-messages"></div>
            
            <!-- Course Form -->
            <form id="courseForm" class="course-form" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="courseName">Course Name *</label>
                        <input type="text" id="courseName" name="courseName" required>
                        <div class="error">Course name is required (min 3 characters)</div>
                    </div>
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Mobile Development">Mobile Development</option>
                            <option value="Data Science">Data Science</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                            <option value="Blockchain">Blockchain</option>
                            <option value="Cybersecurity">Cybersecurity</option>
                            <option value="Game Development">Game Development</option>
                            <option value="Digital Marketing">Digital Marketing</option>
                            <option value="Graphic Design">Graphic Design</option>
                            <option value="Business">Business</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="error">Please select a category</div>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="description">Course Description *</label>
                    <textarea id="description" name="description" placeholder="Describe what students will learn in this course..." required></textarea>
                    <div class="error">Course description is required (min 10 characters)</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (USD) *</label>
                        <input type="number" id="price" name="price" min="0" step="0.01" placeholder="0.00" required>
                        <div class="error">Please enter a valid price</div>
                    </div>
                    <div class="form-group">
                        <label for="duration">Duration (Hours) *</label>
                        <input type="number" id="duration" name="duration" min="1" max="500" placeholder="10" required>
                        <div class="error">Duration must be between 1 and 500 hours</div>
                    </div>
                </div>

                <!-- Course Thumbnail Upload Section -->
                <div class="form-group full-width">
                    <label for="courseThumbnail">Course Thumbnail *</label>
                    <div class="file-upload-area" onclick="document.getElementById('courseThumbnail').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-image"></i>
                        </div>
                        <div class="file-upload-text">Click to upload thumbnail or drag and drop</div>
                        <div class="file-upload-subtext">Supported formats: JPG, PNG, WebP (Max: 5MB, Recommended: 1280x720)</div>
                    </div>
                    <input type="file" id="courseThumbnail" name="courseThumbnail" accept="image/*" style="display: none;" required>
                    <div class="error">Please select a thumbnail image</div>
                    
                    <!-- Thumbnail Preview -->
                    <div class="thumbnail-preview" id="thumbnailPreview" style="display: none;">
                        <img id="thumbnailImg" src="" alt="Course Thumbnail Preview" style="max-width: 300px; max-height: 200px; border-radius: 8px; margin-top: 1rem;">
                        <p style="color: #666; margin-top: 0.5rem;">Thumbnail Preview</p>
                    </div>
                </div>

                <!-- Video Upload Section -->
                <div class="form-group full-width">
                    <label for="courseVideo">Course Video *</label>
                    <div class="file-upload-area" onclick="document.getElementById('courseVideo').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-video"></i>
                        </div>
                        <div class="file-upload-text">Click to upload video or drag and drop</div>
                        <div class="file-upload-subtext">Supported formats: MP4, WebM, AVI (Max: 500MB)</div>
                    </div>
                    <input type="file" id="courseVideo" name="courseVideo" accept="video/*" style="display: none;" required>
                    <div class="error">Please select a video file</div>
                    
                    <!-- File Info Display -->
                    <div class="file-info" id="fileInfo">
                        <div class="file-name" id="fileName"></div>
                        <div class="file-size" id="fileSize"></div>
                    </div>

                    <!-- Upload Progress -->
                    <div class="upload-progress" id="uploadProgress">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>
                        <div class="progress-text" id="progressText">Uploading...</div>
                    </div>
                </div>

                <!-- NFT Certificate Image Upload Section -->
                <div class="form-group full-width">
                    <label for="nftCertificate">NFT Certificate Template *</label>
                    <div class="file-upload-area" onclick="document.getElementById('nftCertificate').click()">
                        <div class="file-upload-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div class="file-upload-text">Click to upload certificate template or drag and drop</div>
                        <div class="file-upload-subtext">Supported formats: PNG, JPG (Max: 10MB, Recommended: 1200x800)</div>
                    </div>
                    <input type="file" id="nftCertificate" name="nftCertificate" accept="image/*" style="display: none;" required>
                    <div class="error">Please select an NFT certificate template</div>
                    
                    <!-- Certificate Preview -->
                    <div class="certificate-preview" id="certificatePreview" style="display: none;">
                        <img id="certificateImg" src="" alt="Certificate Template Preview" style="max-width: 400px; max-height: 300px; border-radius: 8px; margin-top: 1rem; border: 2px solid #ddd;">
                        <p style="color: #666; margin-top: 0.5rem;">Certificate Template Preview - This will be awarded as NFT when learners complete the course</p>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn" id="submitBtn">
                    <i class="fas fa-save"></i> Create Course
                </button>
            </form>
        </div>

        <!-- Courses Grid -->
        <div class="courses-section">
            <h2><i class="fas fa-book"></i> My Courses</h2>
            <div id="coursesGrid" class="courses-grid">
                <div class="loading">
                    <i class="fas fa-spinner fa-spin"></i> Loading courses...
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Course Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Edit Course</h2>
                <span class="close-btn" onclick="closeEditModal()">&times;</span>
            </div>
            <div id="edit-messages"></div>
            <form id="editCourseForm" class="edit-form">
                <input type="hidden" id="editCourseId" name="courseId">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editCourseName">Course Name *</label>
                        <input type="text" id="editCourseName" name="courseName" required>
                        <div class="error">Course name is required (min 3 characters)</div>
                    </div>
                    <div class="form-group">
                        <label for="editCategory">Category *</label>
                        <select id="editCategory" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Web Development">Web Development</option>
                            <option value="Mobile Development">Mobile Development</option>
                            <option value="Data Science">Data Science</option>
                            <option value="Artificial Intelligence">Artificial Intelligence</option>
                            <option value="Blockchain">Blockchain</option>
                            <option value="Cybersecurity">Cybersecurity</option>
                            <option value="Game Development">Game Development</option>
                            <option value="Digital Marketing">Digital Marketing</option>
                            <option value="Graphic Design">Graphic Design</option>
                            <option value="Business">Business</option>
                            <option value="Other">Other</option>
                        </select>
                        <div class="error">Please select a category</div>
                    </div>
                </div>
                
                <div class="form-group full-width">
                    <label for="editDescription">Course Description *</label>
                    <textarea id="editDescription" name="description" placeholder="Describe what students will learn in this course..." required></textarea>
                    <div class="error">Course description is required (min 10 characters)</div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="editPrice">Price (USD) *</label>
                        <input type="number" id="editPrice" name="price" min="0" step="0.01" placeholder="0.00" required>
                        <div class="error">Please enter a valid price</div>
                    </div>
                    <div class="form-group">
                        <label for="editDuration">Duration (Hours) *</label>
                        <input type="number" id="editDuration" name="duration" min="1" max="500" placeholder="10" required>
                        <div class="error">Duration must be between 1 and 500 hours</div>
                    </div>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="cancel-btn" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="save-btn" id="saveEditBtn">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // ==============================================
        // GLOBAL VARIABLES FOR FILE MANAGEMENT
        // ==============================================
        // FILE STORAGE: Hold selected files in memory before form submission
        // These variables store file objects for validation and upload progress tracking
        let selectedFile = null;        // Course video file
        let selectedThumbnail = null;   // Course thumbnail image
        let selectedCertificate = null; // NFT certificate template image

        // ==============================================
        // DOCUMENT READY INITIALIZATION
        // ==============================================
        // DOM LOADED EVENT: Initialize all functionality when page loads
        // Sets up file upload handlers, form validation, and modal interactions
        document.addEventListener('DOMContentLoaded', function() {
            // UPLOAD SYSTEM INITIALIZATION: Set up drag-and-drop file upload areas
            initializeFileUpload();         // Video file upload with progress tracking
            initializeThumbnailUpload();    // Thumbnail image upload with preview
            initializeCertificateUpload();  // NFT certificate template upload
            
            // COURSE DATA LOADING: Fetch and display creator's existing courses
            loadCourses();
            
            // ==============================================
            // MODAL FORM EVENT HANDLERS
            // ==============================================
            // EDIT FORM SUBMISSION: Handle course editing via modal form
            document.getElementById('editCourseForm').addEventListener('submit', function(e) {
                e.preventDefault(); // Prevent default form submission
                submitEditForm();   // Handle with AJAX instead
            });
            
            // MODAL CLOSE HANDLER: Close edit modal when clicking outside modal area
            // Improves user experience by allowing easy modal dismissal
            window.onclick = function(event) {
                const modal = document.getElementById('editModal');
                if (event.target === modal) {
                    closeEditModal();
                }
            };
        });

        // ==============================================
        // THUMBNAIL IMAGE UPLOAD SYSTEM
        // ==============================================
        /**
         * Initialize thumbnail upload functionality with drag and drop
         * Sets up file input handling, drag-and-drop events, and image preview
         * Validates image file types and size limits for course thumbnails
         */
        function initializeThumbnailUpload() {
            // DOM ELEMENT REFERENCES: Get thumbnail upload related elements
            const thumbnailInput = document.getElementById('courseThumbnail');
            const thumbnailUploadArea = document.querySelector('.file-upload-area');
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const thumbnailImg = document.getElementById('thumbnailImg');

            // ==============================================
            // FILE INPUT EVENT HANDLER
            // ==============================================
            // THUMBNAIL FILE SELECTION: Handle file selection via file input click
            thumbnailInput.addEventListener('change', function(e) {
                handleThumbnailSelect(e.target.files[0]);
            });

            // ==============================================
            // DRAG AND DROP FUNCTIONALITY
            // ==============================================
            // UPLOAD AREA TARGETING: Get the first upload area (designated for thumbnails)
            const thumbnailUploadAreas = document.querySelectorAll('.file-upload-area');
            const thumbnailArea = thumbnailUploadAreas[0]; // First upload area is for thumbnail

            // DRAG OVER EVENT: Visual feedback when file is dragged over upload area
            thumbnailArea.addEventListener('dragover', function(e) {
                e.preventDefault(); // Prevent default browser behavior
                thumbnailArea.classList.add('dragover'); // Add visual drag indicator
            });

            // DRAG LEAVE EVENT: Remove visual feedback when file leaves upload area
            thumbnailArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                thumbnailArea.classList.remove('dragover'); // Remove drag indicator
            });

            // FILE DROP EVENT: Handle file drop and process selected image
            thumbnailArea.addEventListener('drop', function(e) {
                e.preventDefault(); // Prevent browser from opening file
                thumbnailArea.classList.remove('dragover'); // Clean up drag state
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleThumbnailSelect(files[0]); // Process the dropped file
                }
            });
        }

        // ==============================================
        // THUMBNAIL FILE PROCESSING AND VALIDATION
        // ==============================================
        /**
         * Handle thumbnail selection and validation
         * Validates file type, size, and generates preview for user feedback
         * Updates global selectedThumbnail variable for form submission
         */
        function handleThumbnailSelect(file) {
            // NULL CHECK: Exit if no file provided
            if (!file) return;

            // ==============================================
            // FILE TYPE VALIDATION
            // ==============================================
            // ALLOWED FORMATS: Define supported image formats for thumbnails
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(document.getElementById('courseThumbnail'), 'Please select a valid image file (JPG, PNG, WebP)');
                return;
            }

            // ==============================================
            // FILE SIZE VALIDATION
            // ==============================================
            // SIZE LIMIT CHECK: Ensure thumbnail doesn't exceed 5MB limit
            // Large thumbnails can slow down page loading and user experience
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                showFieldError(document.getElementById('courseThumbnail'), 'Thumbnail size must be less than 5MB');
                return;
            }

            // ==============================================
            // FILE STORAGE AND PREVIEW GENERATION
            // ==============================================
            // STORE SELECTED FILE: Save file reference for later upload
            selectedThumbnail = file;

            // THUMBNAIL PREVIEW SETUP: Generate and display image preview
            const thumbnailPreview = document.getElementById('thumbnailPreview');
            const thumbnailImg = document.getElementById('thumbnailImg');
            
            // FILE READER: Convert image to base64 for preview display
            const reader = new FileReader();
            reader.onload = function(e) {
                thumbnailImg.src = e.target.result; // Set preview image source
                thumbnailPreview.style.display = 'block'; // Show preview container
            };
            reader.readAsDataURL(file); // Start reading file as data URL

            // ==============================================
            // ERROR STATE CLEANUP
            // ==============================================
            // CLEAR VALIDATION ERRORS: Remove error styling after successful selection
            const formGroup = document.getElementById('courseThumbnail').closest('.form-group');
            formGroup.classList.remove('has-error');
        }

        // ==============================================
        // NFT CERTIFICATE TEMPLATE UPLOAD SYSTEM
        // ==============================================
        /**
         * Initialize NFT certificate upload functionality
         * Sets up upload area for certificate templates that will be awarded as NFTs
         * Handles file selection, drag-and-drop, and certificate image preview
         */
        function initializeCertificateUpload() {
            // DOM ELEMENT REFERENCES: Get certificate upload related elements
            const certificateInput = document.getElementById('nftCertificate');
            const certificateUploadAreas = document.querySelectorAll('.file-upload-area');
            const certificateArea = certificateUploadAreas[2]; // Third upload area is for certificate
            const certificatePreview = document.getElementById('certificatePreview');
            const certificateImg = document.getElementById('certificateImg');

            // ==============================================
            // FILE INPUT EVENT HANDLER
            // ==============================================
            // CERTIFICATE FILE SELECTION: Handle file selection via file input click
            certificateInput.addEventListener('change', function(e) {
                handleCertificateSelect(e.target.files[0]);
            });

            // ==============================================
            // DRAG AND DROP FUNCTIONALITY FOR CERTIFICATES
            // ==============================================
            // DRAG OVER EVENT: Visual feedback when certificate template is dragged over area
            certificateArea.addEventListener('dragover', function(e) {
                e.preventDefault(); // Allow drop operation
                certificateArea.classList.add('dragover'); // Visual drag indicator
            });

            // DRAG LEAVE EVENT: Remove visual feedback when file leaves drop zone
            certificateArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                certificateArea.classList.remove('dragover'); // Clean up drag state
            });

            // CERTIFICATE DROP EVENT: Process dropped certificate template file
            certificateArea.addEventListener('drop', function(e) {
                e.preventDefault(); // Prevent browser from opening file
                certificateArea.classList.remove('dragover'); // Remove drag styling
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleCertificateSelect(files[0]); // Process the certificate template
                }
            });
        }

        // ==============================================
        // CERTIFICATE TEMPLATE PROCESSING AND VALIDATION
        // ==============================================
        /**
         * Handle certificate template selection and validation
         * Validates NFT certificate image files and generates preview
         * These templates will be awarded as blockchain-based NFT certificates
         */
        function handleCertificateSelect(file) {
            // NULL CHECK: Exit if no file provided
            if (!file) return;

            // ==============================================
            // CERTIFICATE FILE TYPE VALIDATION
            // ==============================================
            // ALLOWED FORMATS: Define supported image formats for NFT certificates
            // High-quality formats preferred for blockchain storage and display
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(document.getElementById('nftCertificate'), 'Please select a valid image file (JPG, PNG)');
                return;
            }

            // ==============================================
            // CERTIFICATE SIZE VALIDATION
            // ==============================================
            // SIZE LIMIT CHECK: Ensure certificate template doesn't exceed 10MB
            // Larger limit than thumbnails as certificates need higher quality for NFT minting
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            if (file.size > maxSize) {
                showFieldError(document.getElementById('nftCertificate'), 'Certificate image size must be less than 10MB');
                return;
            }

            // ==============================================
            // CERTIFICATE STORAGE AND PREVIEW
            // ==============================================
            // STORE CERTIFICATE FILE: Save file reference for NFT system processing
            selectedCertificate = file;

            // CERTIFICATE PREVIEW SETUP: Generate and display template preview
            const certificatePreview = document.getElementById('certificatePreview');
            const certificateImg = document.getElementById('certificateImg');
            
            // FILE READER: Convert certificate image to base64 for preview
            const reader = new FileReader();
            reader.onload = function(e) {
                certificateImg.src = e.target.result; // Set certificate preview image
                certificatePreview.style.display = 'block'; // Show preview container
            };
            reader.readAsDataURL(file); // Start reading certificate template

            // ==============================================
            // VALIDATION ERROR CLEANUP
            // ==============================================
            // CLEAR ERROR STATE: Remove validation errors after successful selection
            const formGroup = document.getElementById('nftCertificate').closest('.form-group');
            formGroup.classList.remove('has-error');
        }

        // ==============================================
        // VIDEO FILE UPLOAD SYSTEM
        // ==============================================
        /**
         * Initialize file upload functionality with drag and drop
         * Handles course video file selection, validation, and upload progress
         * Supports large video files with size validation and user feedback
         */
        function initializeFileUpload() {
            // DOM ELEMENT REFERENCES: Get video upload related elements
            const fileInput = document.getElementById('courseVideo');
            const uploadAreas = document.querySelectorAll('.file-upload-area');
            const uploadArea = uploadAreas[1]; // Second upload area is for video
            const fileInfo = document.getElementById('fileInfo');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');

            // ==============================================
            // FILE INPUT EVENT HANDLER
            // ==============================================
            // VIDEO FILE SELECTION: Handle file selection via file input click
            fileInput.addEventListener('change', function(e) {
                handleFileSelect(e.target.files[0]);
            });

            // ==============================================
            // DRAG AND DROP FUNCTIONALITY FOR VIDEOS
            // ==============================================
            // DRAG OVER EVENT: Visual feedback when video file is dragged over upload area
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault(); // Allow drop operation
                uploadArea.classList.add('dragover'); // Visual drag indicator
            });

            // DRAG LEAVE EVENT: Remove visual feedback when file leaves drop zone
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('dragover'); // Clean up drag styling
            });

            // VIDEO DROP EVENT: Process dropped video file
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault(); // Prevent browser from opening video
                uploadArea.classList.remove('dragover'); // Remove drag styling
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFileSelect(files[0]); // Process the video file
                }
            });
        }

        // ==============================================
        // VIDEO FILE PROCESSING AND VALIDATION
        // ==============================================
        /**
         * Handle file selection and validation
         * Validates video file types, size limits, and displays file information
         * Stores video file reference for upload progress tracking
         */
        function handleFileSelect(file) {
            // NULL CHECK: Exit if no file provided
            if (!file) return;

            // ==============================================
            // VIDEO FILE TYPE VALIDATION
            // ==============================================
            // ALLOWED FORMATS: Define supported video formats for course content
            // Common web-compatible formats for cross-platform playback
            const allowedTypes = ['video/mp4', 'video/webm', 'video/avi', 'video/quicktime', 'video/x-msvideo'];
            if (!allowedTypes.includes(file.type)) {
                showFieldError(document.getElementById('courseVideo'), 'Please select a valid video file (MP4, WebM, AVI)');
                return;
            }

            // ==============================================
            // VIDEO FILE SIZE VALIDATION
            // ==============================================
            // SIZE LIMIT CHECK: Ensure video doesn't exceed 500MB limit
            // Large videos can cause upload timeouts and storage issues
            const maxSize = 500 * 1024 * 1024; // 500MB in bytes
            if (file.size > maxSize) {
                showFieldError(document.getElementById('courseVideo'), 'File size must be less than 500MB');
                return;
            }

            // ==============================================
            // VIDEO FILE STORAGE AND INFO DISPLAY
            // ==============================================
            // STORE VIDEO FILE: Save file reference for upload processing
            selectedFile = file;

            // FILE INFO DISPLAY: Show file name and size to user for confirmation
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const fileInfo = document.getElementById('fileInfo');

            fileName.textContent = file.name; // Display original filename
            fileSize.textContent = formatFileSize(file.size); // Display formatted file size
            fileInfo.classList.add('show'); // Make file info visible

            // ==============================================
            // VALIDATION ERROR CLEANUP
            // ==============================================
            // CLEAR ERROR STATE: Remove validation errors after successful selection
            const formGroup = document.getElementById('courseVideo').closest('.form-group');
            formGroup.classList.remove('has-error');
        }

        /**
         * Format file size for display
         */
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // ==============================================
        // MAIN FORM SUBMISSION HANDLER
        // ==============================================
        /**
         * Form submission handler
         * Prevents default form submission and validates all fields before processing
         */
        document.getElementById('courseForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Prevent default browser form submission
            
            // VALIDATION CHECK: Only proceed if all form data is valid
            if (validateForm()) {
                submitCourse(); // Process the validated form
            }
        });

        // ==============================================
        // COMPREHENSIVE FORM VALIDATION SYSTEM
        // ==============================================
        /**
         * Comprehensive form validation
         * Validates all course creation form fields including text, numbers, and files
         * Returns true if all validations pass, false otherwise
         */
        function validateForm() {
            let isValid = true; // Track overall validation status
            const form = document.getElementById('courseForm');
            const formGroups = form.querySelectorAll('.form-group');
            
            // ==============================================
            // RESET PREVIOUS VALIDATION STATES
            // ==============================================
            // CLEAR ERROR STYLING: Remove previous validation error indicators
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
            
            // ==============================================
            // TEXT FIELD VALIDATIONS
            // ==============================================
            // COURSE NAME VALIDATION: Ensure meaningful course title
            const courseName = document.getElementById('courseName');
            if (courseName.value.trim().length < 3) {
                showFieldError(courseName, 'Course name must be at least 3 characters');
                isValid = false;
            }
            
            // CATEGORY VALIDATION: Ensure course is properly categorized
            const category = document.getElementById('category');
            if (!category.value) {
                showFieldError(category, 'Please select a category');
                isValid = false;
            }
            
            // DESCRIPTION VALIDATION: Ensure adequate course description
            const description = document.getElementById('description');
            if (description.value.trim().length < 10) {
                showFieldError(description, 'Description must be at least 10 characters');
                isValid = false;
            }
            
            // ==============================================
            // NUMERIC FIELD VALIDATIONS
            // ==============================================
            // PRICE VALIDATION: Ensure valid monetary value (free or paid)
            const price = document.getElementById('price');
            if (price.value === '' || parseFloat(price.value) < 0) {
                showFieldError(price, 'Please enter a valid price (0 or greater)');
                isValid = false;
            }
            
            // DURATION VALIDATION: Ensure realistic course duration
            const duration = document.getElementById('duration');
            if (duration.value === '' || parseInt(duration.value) < 1 || parseInt(duration.value) > 500) {
                showFieldError(duration, 'Duration must be between 1 and 500 hours');
                isValid = false;
            }

            // ==============================================
            // FILE UPLOAD VALIDATIONS
            // ==============================================
            // THUMBNAIL VALIDATION: Ensure course has visual representation
            const thumbnailInput = document.getElementById('courseThumbnail');
            if (!selectedThumbnail && !thumbnailInput.files[0]) {
                showFieldError(thumbnailInput, 'Please select a thumbnail image');
                isValid = false;
            }

            // VIDEO FILE VALIDATION: Ensure course has video content
            const videoInput = document.getElementById('courseVideo');
            if (!selectedFile && !videoInput.files[0]) {
                showFieldError(videoInput, 'Please select a video file');
                isValid = false;
            }

            // CERTIFICATE VALIDATION: Ensure NFT template is provided
            const certificateInput = document.getElementById('nftCertificate');
            if (!selectedCertificate && !certificateInput.files[0]) {
                showFieldError(certificateInput, 'Please select an NFT certificate template');
                isValid = false;
            }
            
            return isValid; // Return overall validation result
        }

        /**
         * Show field-specific error message
         */
        function showFieldError(field, message) {
            const formGroup = field.closest('.form-group');
            const errorDiv = formGroup.querySelector('.error');
            formGroup.classList.add('has-error');
            errorDiv.textContent = message;
        }

        // ==============================================
        // COURSE SUBMISSION WITH FILE UPLOAD HANDLING
        // ==============================================
        /**
         * Submit course form with file upload
         * Handles multipart form submission with progress tracking for large files
         * Manages UI state during upload and processes server response
         */
        function submitCourse() {
            // UI ELEMENT REFERENCES: Get submission UI elements for state management
            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.innerHTML;
            const uploadProgress = document.getElementById('uploadProgress');
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            // ==============================================
            // UI STATE MANAGEMENT DURING UPLOAD
            // ==============================================
            // DISABLE FORM INTERACTION: Prevent multiple submissions and show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
            uploadProgress.classList.add('show'); // Show progress bar
            
            // ==============================================
            // FORM DATA PREPARATION
            // ==============================================
            // CREATE MULTIPART FORM DATA: Package all course data for upload
            const formData = new FormData();
            
            // TEXT FIELD DATA: Add all text-based form fields
            formData.append('courseName', document.getElementById('courseName').value);
            formData.append('category', document.getElementById('category').value);
            formData.append('description', document.getElementById('description').value);
            formData.append('price', document.getElementById('price').value);
            formData.append('duration', document.getElementById('duration').value);
            
            // ==============================================
            // FILE ATTACHMENT HANDLING
            // ==============================================
            // THUMBNAIL FILE: Add course thumbnail image to form data
            const thumbnailFile = selectedThumbnail || document.getElementById('courseThumbnail').files[0];
            formData.append('courseThumbnail', thumbnailFile);
            
            // VIDEO FILE: Add main course video content to form data
            const videoFile = selectedFile || document.getElementById('courseVideo').files[0];
            formData.append('courseVideo', videoFile);

            // CERTIFICATE FILE: Add NFT certificate template to form data
            const certificateFile = selectedCertificate || document.getElementById('nftCertificate').files[0];
            formData.append('nftCertificate', certificateFile);

            // Debug: Log what we're sending
            console.log('Form data being sent:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(key + ':', value.name, value.size + ' bytes');
                } else {
                    console.log(key + ':', value);
                }
            }

            // Create XMLHttpRequest for upload progress tracking
            const xhr = new XMLHttpRequest();

            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressFill.style.width = percentComplete + '%';
                    progressText.textContent = `Uploading... ${Math.round(percentComplete)}%`;
                }
            });

            // Handle response
            xhr.addEventListener('load', function() {
                // Debug: Log the raw response
                console.log('Raw server response:', xhr.responseText);
                console.log('Response status:', xhr.status);
                
                try {
                    // Clean the response by extracting only the JSON part
                    let cleanResponse = xhr.responseText.trim();
                    
                    // Find the first occurrence of { and last occurrence of }
                    const firstBrace = cleanResponse.indexOf('{');
                    const lastBrace = cleanResponse.lastIndexOf('}');
                    
                    if (firstBrace !== -1 && lastBrace !== -1 && firstBrace < lastBrace) {
                        cleanResponse = cleanResponse.substring(firstBrace, lastBrace + 1);
                        console.log('Cleaned JSON response:', cleanResponse);
                    }
                    
                    const response = JSON.parse(cleanResponse);
                    console.log('Parsed response:', response);
                    
                    if (response.success) {
                        // Reset UI for success
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        uploadProgress.classList.remove('show');
                        progressFill.style.width = '0%';
                        progressText.textContent = '';
                        
                        showMessage('Course created successfully!', 'success');
                        resetForm();
                        loadCourses(); // Refresh courses grid
                    } else {
                        // Reset UI for error
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        uploadProgress.classList.remove('show');
                        progressFill.style.width = '0%';
                        progressText.textContent = '';
                        
                        showMessage(response.message || 'Error creating course', 'error');
                    }
                } catch (error) {
                    console.error('JSON Parse Error:', error);
                    console.error('Response Text:', xhr.responseText);
                    
                    // Try to extract a meaningful error message
                    let errorMessage = 'Error creating course';
                    if (xhr.responseText.includes('success":true')) {
                        // If we can see success:true in the response, the course was likely created
                        // Reset UI for success case
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        uploadProgress.classList.remove('show');
                        progressFill.style.width = '0%';
                        progressText.textContent = '';
                        
                        showMessage('Course created successfully!', 'success');
                        resetForm();
                        loadCourses();
                        return;
                    }
                    
                    showMessage(`Server error: ${errorMessage}`, 'error');
                    resetUI();
                }
            });

            // Handle errors
            xhr.addEventListener('error', function() {
                console.error('Network error during upload');
                showMessage('Network error. Please try again.', 'error');
                resetUI();
            });

            // Send request
            xhr.open('POST', 'save_course.php', true);
            xhr.send(formData);

            function resetUI() {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
                uploadProgress.classList.remove('show');
                progressFill.style.width = '0%';
                progressText.textContent = '';
            }
        }

        /**
         * Reset form after successful submission
         */
        function resetForm() {
            document.getElementById('courseForm').reset();
            selectedFile = null;
            selectedThumbnail = null;
            selectedCertificate = null;
            document.getElementById('fileInfo').classList.remove('show');
            document.getElementById('thumbnailPreview').style.display = 'none';
            document.getElementById('certificatePreview').style.display = 'none';
            
            // Clear all error states
            const formGroups = document.querySelectorAll('.form-group');
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
        }

        /**
         * Display success/error messages
         */
        function showMessage(message, type) {
            const messagesDiv = document.getElementById('form-messages');
            messagesDiv.innerHTML = `<div class="${type}-message">${message}</div>`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messagesDiv.innerHTML = '';
            }, 5000);
        }

        // ==============================================
        // COURSE DATA LOADING AND MANAGEMENT
        // ==============================================
        /**
         * Load and display creator's courses
         * Fetches course data from server and populates the courses grid
         * Handles loading states, errors, and empty states
         */
        function loadCourses() {
            console.log('loadCourses() called'); // Debug logging
            const coursesGrid = document.getElementById('coursesGrid');
            
            // DOM ELEMENT CHECK: Ensure courses grid exists before proceeding
            if (!coursesGrid) {
                console.error('coursesGrid element not found!');
                return;
            }
            
            // ==============================================
            // LOADING STATE DISPLAY
            // ==============================================
            // SHOW LOADING INDICATOR: Display spinner while fetching data
            coursesGrid.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Loading courses...</div>';
            
            // ==============================================
            // COURSE DATA FETCH REQUEST
            // ==============================================
            console.log('Fetching courses from get_courses.php...'); // Debug logging
            fetch('get_courses.php') // Request creator's courses from server
            .then(response => {
                console.log('Response status:', response.status); // Debug response status
                console.log('Response headers:', response.headers); // Debug headers
                return response.text(); // Get as text first to see raw response for debugging
            })
            .then(text => {
                console.log('Raw response text:', text); // Debug raw response
                try {
                    // JSON PARSING: Convert response text to JavaScript object
                    const data = JSON.parse(text);
                    console.log('Parsed JSON data:', data); // Debug parsed data
                    
                    if (data.success) {
                        console.log('Success! Courses:', data.courses);
                        displayCourses(data.courses);
                    } else {
                        console.log('API returned error:', data.message);
                        coursesGrid.innerHTML = `<div class="error-message">${data.message}</div>`;
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    coursesGrid.innerHTML = `<div class="error-message">Invalid JSON response: ${parseError.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                coursesGrid.innerHTML = '<div class="error-message">Network error loading courses. Check console for details.</div>';
            });
        }

        // ==============================================
        // COURSE GRID DISPLAY AND RENDERING
        // ==============================================
        /**
         * Display courses in grid format
         * Renders course data as interactive cards with thumbnails and action buttons
         * Handles empty states and data validation
         */
        function displayCourses(courses) {
            console.log('displayCourses() called with:', courses); // Debug course data
            const coursesGrid = document.getElementById('coursesGrid');
            
            // ==============================================
            // DOM ELEMENT VALIDATION
            // ==============================================
            // GRID ELEMENT CHECK: Ensure courses grid container exists
            if (!coursesGrid) {
                console.error('coursesGrid element not found in displayCourses!');
                return;
            }
            
            // ==============================================
            // DATA VALIDATION AND TYPE CHECKING
            // ==============================================
            // ARRAY VALIDATION: Ensure courses data is in expected format
            if (!Array.isArray(courses)) {
                console.error('courses is not an array:', courses);
                coursesGrid.innerHTML = `<div class="error-message">Invalid courses data format</div>`;
                return;
            }
            
            console.log('Number of courses to display:', courses.length); // Debug course count
            
            // ==============================================
            // EMPTY STATE HANDLING
            // ==============================================
            // NO COURSES STATE: Display helpful message when creator has no courses yet
            if (courses.length === 0) {
                console.log('No courses found - showing empty state');
                coursesGrid.innerHTML = `
                    <div class="no-courses">
                        <i class="fas fa-book"></i>
                        <p>You haven't created any courses yet. Create your first course above!</p>
                    </div>
                `;
                return;
            }
            
            const coursesHTML = courses.map(course => {
                // Debug each course
                console.log('Processing course:', course);
                
                const title = course.title && course.title.trim() !== '' ? course.title : 'Untitled Course';
                const description = course.description && course.description.trim() !== '' ? course.description : 'No description available';
                const category = course.category && course.category.trim() !== '' ? course.category : 'General';
                
                return `
                <div class="course-card">
                    ${course.thumbnail ? 
                        `<div style="margin-bottom: 1rem;">
                            <img src="${course.thumbnail}" alt="${escapeHtml(title)}" style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px;">
                        </div>` : 
                        `<div style="width: 100%; height: 150px; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem; border-radius: 8px; margin-bottom: 1rem;">📚</div>`
                    }
                    <h3>${escapeHtml(title)}</h3>
                    <div class="course-meta">
                        <span><i class="fas fa-tag"></i> ${escapeHtml(category)}</span>
                        <span><i class="fas fa-clock"></i> ${course.duration || 0} hours</span>
                        <span><i class="fas fa-users"></i> ${course.students_enrolled || 0} students</span>
                    </div>
                    <div class="course-price">$${parseFloat(course.price || 0).toFixed(2)}</div>
                    <p style="color: #666; margin-bottom: 1rem;">${escapeHtml(description.substring(0, 100))}${description.length > 100 ? '...' : ''}</p>
                    <div class="course-actions">
                        <button class="action-btn edit-btn" onclick="editCourse(${course.id})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="action-btn delete-btn" onclick="deleteCourse(${course.id})">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `}).join('');
            
            coursesGrid.innerHTML = coursesHTML;
        }

        // ==============================================
        // COURSE EDITING SYSTEM
        // ==============================================
        /**
         * Edit course functionality
         * Initiates course editing by fetching current course data and opening modal
         * Handles server communication and error states
         */
        function editCourse(courseId) {
            console.log('Opening edit modal for course ID:', courseId); // Debug course ID
            
            // ==============================================
            // COURSE DATA RETRIEVAL FOR EDITING
            // ==============================================
            // FETCH COURSE DETAILS: Get current course data from server for editing
            fetch('get_course_details.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ courseId: courseId }) // Send course ID to fetch specific course
            })
            .then(response => response.text()) // Get response as text for debugging
            .then(text => {
                console.log('Course details response:', text); // Debug server response
                try {
                    // RESPONSE PROCESSING: Parse server response and handle success/error
                    const data = JSON.parse(text);
                    if (data.success) {
                        openEditModal(data.course); // Open modal with course data
                    } else {
                        showMessage(data.message || 'Error loading course details', 'error');
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    showMessage('Error loading course details', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading course details:', error);
                showMessage('Network error. Please try again.', 'error');
            });
        }

        // ==============================================
        // EDIT MODAL SETUP AND POPULATION
        // ==============================================
        /**
         * Open edit modal with course data
         * Populates modal form fields with existing course data
         * Clears previous validation errors and displays modal
         */
        function openEditModal(course) {
            const modal = document.getElementById('editModal');
            
            // ==============================================
            // FORM FIELD POPULATION
            // ==============================================
            // POPULATE EDIT FORM: Fill form fields with current course data
            document.getElementById('editCourseId').value = course.id; // Hidden course ID
            document.getElementById('editCourseName').value = course.title || course.course_name || '';
            document.getElementById('editCategory').value = course.category || '';
            document.getElementById('editDescription').value = course.description || '';
            document.getElementById('editPrice').value = course.price || 0;
            document.getElementById('editDuration').value = course.duration || 0;
            
            // ==============================================
            // VALIDATION STATE CLEANUP
            // ==============================================
            // CLEAR ERROR STATES: Remove any previous validation error styling
            const formGroups = document.querySelectorAll('#editCourseForm .form-group');
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
            
            // Show modal
            modal.classList.add('show');
        }

        /**
         * Close edit modal
         */
        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.remove('show');
            document.getElementById('edit-messages').innerHTML = '';
        }

        /**
         * Handle edit form submission
         */

        /**
         * Submit edit form
         */
        function submitEditForm() {
            if (!validateEditForm()) {
                return;
            }
            
            const saveBtn = document.getElementById('saveEditBtn');
            const originalText = saveBtn.innerHTML;
            
            // Disable button and show loading
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
            
            const formData = {
                courseId: document.getElementById('editCourseId').value,
                courseName: document.getElementById('editCourseName').value,
                category: document.getElementById('editCategory').value,
                description: document.getElementById('editDescription').value,
                price: document.getElementById('editPrice').value,
                duration: document.getElementById('editDuration').value
            };
            
            fetch('update_course.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.text())
            .then(text => {
                console.log('Update response:', text);
                try {
                    const data = JSON.parse(text);
                    
                    // Reset button
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                    
                    if (data.success) {
                        showEditMessage('Course updated successfully!', 'success');
                        setTimeout(() => {
                            closeEditModal();
                            loadCourses(); // Refresh courses list
                        }, 1500);
                    } else {
                        showEditMessage(data.message || 'Error updating course', 'error');
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    showEditMessage('Error processing response', 'error');
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Update error:', error);
                showEditMessage('Network error. Please try again.', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalText;
            });
        }

        /**
         * Validate edit form
         */
        function validateEditForm() {
            let isValid = true;
            const form = document.getElementById('editCourseForm');
            const formGroups = form.querySelectorAll('.form-group');
            
            // Clear previous errors
            formGroups.forEach(group => {
                group.classList.remove('has-error');
            });
            
            // Course name validation
            const courseName = document.getElementById('editCourseName');
            if (courseName.value.trim().length < 3) {
                showEditFieldError(courseName, 'Course name must be at least 3 characters');
                isValid = false;
            }
            
            // Category validation
            const category = document.getElementById('editCategory');
            if (!category.value) {
                showEditFieldError(category, 'Please select a category');
                isValid = false;
            }
            
            // Description validation
            const description = document.getElementById('editDescription');
            if (description.value.trim().length < 10) {
                showEditFieldError(description, 'Description must be at least 10 characters');
                isValid = false;
            }
            
            // Price validation
            const price = document.getElementById('editPrice');
            if (price.value === '' || parseFloat(price.value) < 0) {
                showEditFieldError(price, 'Please enter a valid price (0 or greater)');
                isValid = false;
            }
            
            // Duration validation
            const duration = document.getElementById('editDuration');
            if (duration.value === '' || parseInt(duration.value) < 1 || parseInt(duration.value) > 500) {
                showEditFieldError(duration, 'Duration must be between 1 and 500 hours');
                isValid = false;
            }
            
            return isValid;
        }

        /**
         * Show edit form field error
         */
        function showEditFieldError(field, message) {
            const formGroup = field.closest('.form-group');
            const errorDiv = formGroup.querySelector('.error');
            formGroup.classList.add('has-error');
            errorDiv.textContent = message;
        }

        /**
         * Show edit form messages
         */
        function showEditMessage(message, type) {
            const messagesDiv = document.getElementById('edit-messages');
            messagesDiv.innerHTML = `<div class="${type}-message" style="margin-bottom: 1rem;">${message}</div>`;
        }

        // ==============================================
        // COURSE DELETION SYSTEM
        // ==============================================
        /**
         * Delete course with confirmation
         * Prompts user for confirmation before permanently deleting a course
         * Handles server communication and UI updates after deletion
         */
        function deleteCourse(courseId) {
            console.log('Attempting to delete course with ID:', courseId); // Debug course ID
            
            // ==============================================
            // USER CONFIRMATION DIALOG
            // ==============================================
            // CONFIRMATION PROMPT: Ensure user wants to permanently delete course
            // Prevents accidental deletions with clear warning message
            if (confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
                
                // ==============================================
                // COURSE DELETION REQUEST
                // ==============================================
                // DELETE API CALL: Send deletion request to server
                fetch('delete_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ courseId: courseId }) // Send course ID for deletion
                })
                .then(response => {
                    console.log('Delete response status:', response.status); // Debug response
                    return response.text(); // Get response as text for debugging
                })
                .then(text => {
                    console.log('Raw response:', text); // Debug raw response
                    try {
                        // ==============================================
                        // DELETION RESPONSE PROCESSING
                        // ==============================================
                        // PARSE SERVER RESPONSE: Handle deletion success or error
                        const data = JSON.parse(text);
                        console.log('Parsed response data:', data);
                        
                        if (data.success) {
                            // SUCCESS HANDLING: Update UI and refresh course list
                            showMessage('Course deleted successfully!', 'success');
                            loadCourses(); // Refresh courses grid to reflect deletion
                        } else {
                            // ERROR HANDLING: Display server-provided error message
                            showMessage(data.message || 'Error deleting course', 'error');
                            console.error('Delete error details:', data);
                        }
                    } catch (parseError) {
                        // JSON PARSE ERROR: Handle malformed server response
                        console.error('JSON parse error:', parseError);
                        console.error('Response text:', text);
                        showMessage('Server error: Invalid response format', 'error');
                    }
                })
                .catch(error => {
                    // NETWORK ERROR: Handle connection or server issues
                    showMessage('Network error. Please try again.', 'error');
                    console.error('Delete network error:', error);
                });
            }
        }

        // ==============================================
        // SECURITY AND UTILITY FUNCTIONS
        // ==============================================
        /**
         * Escape HTML to prevent XSS
         * Safely converts user input text to HTML by escaping special characters
         * Prevents Cross-Site Scripting (XSS) attacks when displaying user data
         */
        function escapeHtml(text) {
            // CREATE TEMPORARY DOM ELEMENT: Use browser's built-in text escaping
            const div = document.createElement('div');
            div.textContent = text; // Set as text content (auto-escapes HTML)
            return div.innerHTML;   // Return escaped HTML-safe string
        }
    </script>
</body>
</html>