<?php
session_start();
include 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.html');
    exit();
}


// ==============================================
// DASHBOARD STATISTICS CALCULATION
// ==============================================

// CREATOR MANAGEMENT: Get total number of creators
// This query counts all registered creators in the system for dashboard display
$creators_query = "SELECT COUNT(*) as count FROM creators";
$creators_result = mysqli_query($conn, $creators_query);
$creators_count = mysqli_fetch_assoc($creators_result)['count'];

// LEARNER MANAGEMENT: Get total number of learners
// This query counts all registered learners in the system for dashboard display
$learners_query = "SELECT COUNT(*) as count FROM learners";
$learners_result = mysqli_query($conn, $learners_query);
$learners_count = mysqli_fetch_assoc($learners_result)['count'];

// COMBINED USER STATISTICS: Calculate total platform users
// Combines creators and learners for overall platform growth metrics
$total_users = $creators_count + $learners_count;

// Active Courses (Published courses)
$active_courses_query = "SELECT COUNT(*) as count FROM courses WHERE status = 'published'";
$active_courses_result = mysqli_query($conn, $active_courses_query);
$active_courses = mysqli_fetch_assoc($active_courses_result)['count'];

// ==============================================
// NFT CERTIFICATE TRACKING
// ==============================================
// NFT CERTIFICATES COUNT: Total certificates issued for completed courses
// Counts all enrollments where learner has completed the course (completed = 1)
// This represents the total number of NFT certificates that can be issued
$nft_certificates_query = "SELECT COUNT(*) as count FROM enrollments WHERE completed = 1";
$nft_certificates_result = mysqli_query($conn, $nft_certificates_query);
$nft_certificates = mysqli_fetch_assoc($nft_certificates_result)['count'];

// ==============================================
// RECENT ACTIVITY FEED MANAGEMENT
// ==============================================
// Provides paginated activity feed for the admin dashboard
// Shows recent enrollments, course creations, and course completions

// ACTIVITY PAGINATION: Set items per page for activity feed
$activities_per_page = 5;

// PAGE VALIDATION: Get current page number, ensure minimum value is 1
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// PAGINATION OFFSET: Calculate database offset for LIMIT clause
$offset = ($page - 1) * $activities_per_page;

// ==============================================
// ACTIVITY COUNT QUERY FOR PAGINATION
// ==============================================
// TOTAL ACTIVITY COUNT: Count all platform activities for pagination calculation
// Combines three types of activities:
// 1. Course enrollments
// 2. New course creations  
// 3. Course completions
$total_activities_query = "
    SELECT COUNT(*) as total FROM (
        -- Count course enrollments
        SELECT e.enrolled_at as created_at FROM enrollments e
        JOIN learners l ON e.learner_id = l.id
        JOIN courses c ON e.course_id = c.id
        
        UNION ALL
        
        -- Count new course creations
        SELECT c.created_at FROM courses c
        JOIN creators cr ON c.creator_id = cr.id
        
        UNION ALL
        
        -- Count course completions (only where completion date exists)
        SELECT e.completed_at as created_at FROM enrollments e
        JOIN learners l ON e.learner_id = l.id
        JOIN courses c ON e.course_id = c.id
        WHERE e.completed = 1 AND e.completed_at IS NOT NULL
    ) as all_activities
";
$total_result = mysqli_query($conn, $total_activities_query);
$total_activities = mysqli_fetch_assoc($total_result)['total'];

// PAGINATION PAGES: Calculate total number of pages needed
$total_pages = ceil($total_activities / $activities_per_page);

// ==============================================
// MAIN ACTIVITY FEED QUERY
// ==============================================
// ACTIVITY FEED QUERY: Retrieve recent platform activities with pagination
// Combines three activity types with standardized output format:
// 1. Course enrollments - learner enrolls in a course
// 2. Course creations - creator publishes a new course
// 3. Course completions - learner completes a course and earns certificate
$recent_activity_query = "
    -- ENROLLMENT ACTIVITIES: When learners enroll in courses
    SELECT 'enrollment' as action_type, 
           l.full_name as user_name, 
           c.course_name as item_name,
           e.enrolled_at as created_at,
           'enrolled in course' as action_description
    FROM enrollments e
    JOIN learners l ON e.learner_id = l.id
    JOIN courses c ON e.course_id = c.id
    
    UNION ALL
    
    -- COURSE CREATION ACTIVITIES: When creators publish new courses
    SELECT 'course_creation' as action_type,
           cr.full_name as user_name,
           c.course_name as item_name,
           c.created_at,
           'created course' as action_description
    FROM courses c
    JOIN creators cr ON c.creator_id = cr.id
    
    UNION ALL
    
    -- COMPLETION ACTIVITIES: When learners complete courses (NFT certificate eligible)
    SELECT 'completion' as action_type,
           l.full_name as user_name,
           c.course_name as item_name,
           e.completed_at as created_at,
           'completed course' as action_description
    FROM enrollments e
    JOIN learners l ON e.learner_id = l.id
    JOIN courses c ON e.course_id = c.id
    WHERE e.completed = 1 AND e.completed_at IS NOT NULL
    
    -- Sort by most recent activity first and apply pagination
    ORDER BY created_at DESC
    LIMIT $activities_per_page OFFSET $offset
";

// ==============================================
// EXECUTE ACTIVITY QUERY & BUILD RESULTS
// ==============================================
// Execute activity query and populate results array for dashboard display
$recent_activity_result = mysqli_query($conn, $recent_activity_query);
$recent_activities = [];

if ($recent_activity_result) {
    // POPULATE ACTIVITY ARRAY: Build array of recent activities for template rendering
    while ($row = mysqli_fetch_assoc($recent_activity_result)) {
        $recent_activities[] = $row;
    }
}

// ==============================================
// USER MANAGEMENT STATISTICS & ANALYTICS
// ==============================================
// This section calculates key user metrics for the admin dashboard
// Provides insights into platform usage and growth trends

// CREATOR COUNT: Total number of registered creators
// Re-uses the previously calculated creators count for dashboard display
$total_creators = $creators_count; // Already calculated above

// LEARNER COUNT: Total number of registered learners
// Re-uses the previously calculated learners count for dashboard display
$total_learners = $learners_count; // Already calculated above

// ==============================================
// DAILY ACTIVITY TRACKING
// ==============================================
// ACTIVE USERS TODAY: Users who performed any action today
// Tracks platform engagement by counting users who:
// 1. Enrolled in courses today
// 2. Created courses today
// 3. Completed courses today
$active_today_query = "
    SELECT COUNT(DISTINCT user_id) as count FROM (
        -- Learners who enrolled in courses today
        SELECT learner_id as user_id FROM enrollments WHERE DATE(enrolled_at) = CURDATE()
        UNION
        -- Creators who published courses today
        SELECT creator_id as user_id FROM courses WHERE DATE(created_at) = CURDATE()
        UNION
        -- Learners who completed courses today
        SELECT learner_id as user_id FROM enrollments WHERE completed = 1 AND DATE(completed_at) = CURDATE()
    ) as active_users
";
$active_today_result = mysqli_query($conn, $active_today_query);
$active_today = mysqli_fetch_assoc($active_today_result)['count'];

// ==============================================
// WEEKLY GROWTH TRACKING
// ==============================================
// NEW REGISTRATIONS THIS WEEK: Platform growth metric
// Combines new creator and learner registrations from the past 7 days
// Used to track user acquisition and platform growth trends
$new_this_week_query = "
    SELECT COUNT(*) as count FROM (
        -- New creators registered in the last 7 days
        SELECT created_at FROM creators WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        UNION ALL
        -- New learners registered in the last 7 days
        SELECT created_at FROM learners WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ) as new_users
";
$new_this_week_result = mysqli_query($conn, $new_this_week_query);
$new_this_week = mysqli_fetch_assoc($new_this_week_result)['count'];

// ==============================================
// USER MANAGEMENT PAGINATION & FILTERING
// ==============================================
// Handles displaying users in manageable chunks with filtering options

// PAGINATION CONFIGURATION: Set number of users to display per page
$users_per_page = 5;

// CURRENT PAGE VALIDATION: Get and validate current page number
// Ensures page number is always at least 1 to prevent invalid queries
$user_page = isset($_GET['user_page']) ? max(1, intval($_GET['user_page'])) : 1;

// PAGINATION OFFSET CALCULATION: Calculate database offset for LIMIT clause
$user_offset = ($user_page - 1) * $users_per_page;

// USER ROLE FILTER: Get selected filter (all users, creators only, or learners only)
$user_filter = isset($_GET['user_filter']) ? $_GET['user_filter'] : 'all';

// ==============================================
// FILTER CONDITION BUILDER
// ==============================================
// BUILD DYNAMIC WHERE CLAUSE: Create filter condition based on user selection
// Allows admin to view all users, only creators, or only learners
$user_filter_condition = "";
if ($user_filter == 'creators') {
    $user_filter_condition = "WHERE role = 'creator'";
} elseif ($user_filter == 'learners') {
    $user_filter_condition = "WHERE role = 'learner'";
}
// Note: No condition added for 'all' filter - shows all users

// ==============================================
// PAGINATION COUNT QUERY
// ==============================================
// TOTAL USER COUNT: Get total number of users matching current filter
// Combines creators and learners tables, then applies filter
// Used to calculate total number of pages needed for pagination
$total_users_query = "
    SELECT COUNT(*) as total FROM (
        -- Get all creators with standardized role field
        SELECT id, full_name, email, created_at, 'creator' as role FROM creators
        UNION ALL
        -- Get all learners with standardized role field
        SELECT id, full_name, email, created_at, 'learner' as role FROM learners
    ) as all_users
    $user_filter_condition
";
$total_users_result = mysqli_query($conn, $total_users_query);
$total_users_count = mysqli_fetch_assoc($total_users_result)['total'];

// PAGINATION CALCULATION: Calculate total number of pages needed
$total_user_pages = ceil($total_users_count / $users_per_page);

// ==============================================
// MAIN USER LISTING QUERY WITH ACTIVITY METRICS
// ==============================================
// Builds different queries based on selected filter and includes activity data

if ($user_filter == 'creators') {
    // CREATORS ONLY QUERY: Show only creators with their course statistics
    // Includes course count and last course creation activity
    $users_query = "
        SELECT id, full_name, email, created_at, 'creator' as role,
               -- Count total courses created by this creator
               (SELECT COUNT(*) FROM courses WHERE creator_id = creators.id) as course_count,
               -- Get timestamp of most recently created course
               (SELECT MAX(created_at) FROM courses WHERE creator_id = creators.id) as last_activity
        FROM creators
        ORDER BY created_at DESC
        LIMIT $users_per_page OFFSET $user_offset
    ";
    
} elseif ($user_filter == 'learners') {
    // LEARNERS ONLY QUERY: Show only learners with their enrollment statistics
    // Includes enrollment count and last enrollment activity
    $users_query = "
        SELECT id, full_name, email, created_at, 'learner' as role,
               -- Count total course enrollments by this learner
               (SELECT COUNT(*) FROM enrollments WHERE learner_id = learners.id) as course_count,
               -- Get timestamp of most recent enrollment
               (SELECT MAX(enrolled_at) FROM enrollments WHERE learner_id = learners.id) as last_activity
        FROM learners
        ORDER BY created_at DESC
        LIMIT $users_per_page OFFSET $user_offset
    ";
    
} else {
    // ALL USERS QUERY: Combine both creators and learners with their respective metrics
    // Uses UNION to merge creators (with course counts) and learners (with enrollment counts)
    $users_query = "
        SELECT id, full_name, email, created_at, 'creator' as role,
               -- Creator activity: count of courses created
               (SELECT COUNT(*) FROM courses WHERE creator_id = creators.id) as course_count,
               -- Creator activity: last course creation timestamp
               (SELECT MAX(created_at) FROM courses WHERE creator_id = creators.id) as last_activity
        FROM creators
        
        UNION ALL
        
        SELECT id, full_name, email, created_at, 'learner' as role,
               -- Learner activity: count of course enrollments
               (SELECT COUNT(*) FROM enrollments WHERE learner_id = learners.id) as course_count,
               -- Learner activity: last enrollment timestamp
               (SELECT MAX(enrolled_at) FROM enrollments WHERE learner_id = learners.id) as last_activity
        FROM learners
        
        ORDER BY created_at DESC
        LIMIT $users_per_page OFFSET $user_offset
    ";
}

// ==============================================
// EXECUTE USER QUERY & BUILD RESULTS ARRAY
// ==============================================
// Execute the query and build array of user data for display
$users_result = mysqli_query($conn, $users_query);
$users_list = [];

if ($users_result) {
    // POPULATE USERS ARRAY: Fetch all matching users into array for template rendering
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users_list[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Learning Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a1a;
            color: #e0e0e0;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d1b2e 100%);
            border-right: 2px solid #dc3545;
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 15px rgba(220, 53, 69, 0.3);
        }

        .sidebar.collapsed {
            transform: translateX(-260px);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(220, 53, 69, 0.4);
        }

        .sidebar-header h2 {
            color: #dc3545;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
        }

        .sidebar-header p {
            color: rgba(224, 224, 224, 0.8);
            font-size: 0.9rem;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            margin: 5px 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: rgba(224, 224, 224, 0.9);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(220, 53, 69, 0.2);
            border-left-color: #dc3545;
            color: #dc3545;
            box-shadow: inset 0 0 10px rgba(220, 53, 69, 0.1);
        }

        .nav-icon {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            left: 260px;
            right: 0;
            height: 70px;
            background: #2d2d2d;
            border-bottom: 2px solid #dc3545;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.2);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            z-index: 999;
            transition: left 0.3s ease;
        }

        .header.full-width {
            left: 0;
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #dc3545;
            cursor: pointer;
            padding: 5px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #3d3d3d;
            border: 1px solid #555;
            border-radius: 25px;
            padding: 8px 20px;
            width: 300px;
            transition: all 0.3s ease;
        }

        .search-box:focus-within {
            box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.3);
            background: #4d4d4d;
            border-color: #dc3545;
        }

        .search-box input {
            border: none;
            background: none;
            outline: none;
            flex: 1;
            padding: 5px 10px;
            font-size: 0.9rem;
            color: #e0e0e0;
        }

        .search-box input::placeholder {
            color: #aaa;
        }

        .search-icon {
            color: #dc3545;
            margin-right: 10px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification {
            position: relative;
            background: none;
            border: none;
            font-size: 1.3rem;
            color: #aaa;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .notification:hover {
            background: #3d3d3d;
            color: #dc3545;
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            width: 8px;
            height: 8px;
            background: #dc3545;
            border-radius: 50%;
        }

        /* Notification Dropdown Styles */
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 380px;
            max-height: 500px;
            background: #2d2d2d;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
            border: 1px solid #dc3545;
            z-index: 1000;
            display: none;
            overflow: hidden;
        }

        .notification-dropdown.show {
            display: block;
            animation: notificationSlideIn 0.3s ease-out;
        }

        @keyframes notificationSlideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 20px 15px 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .notification-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
        }

        .mark-all-read {
            background: none;
            border: none;
            color: #2c5aa0;
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: background 0.2s ease;
        }

        .mark-all-read:hover {
            background: rgba(44, 90, 160, 0.1);
        }

        .notification-list {
            max-height: 350px;
            overflow-y: auto;
        }

        .notification-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 15px 20px;
            border-bottom: 1px solid #f8f9fa;
            transition: background 0.2s ease;
            cursor: pointer;
        }

        .notification-item:hover {
            background: #f8f9fa;
        }

        .notification-item.unread {
            background: rgba(44, 90, 160, 0.02);
            border-left: 3px solid #2c5aa0;
        }

        .notification-item.read {
            opacity: 0.7;
        }

        .notification-icon {
            font-size: 20px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .notification-content {
            flex: 1;
            min-width: 0;
        }

        .notification-title {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .notification-message {
            color: #666;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 6px;
        }

        .notification-time {
            color: #999;
            font-size: 11px;
        }

        .notification-actions {
            display: flex;
            flex-direction: column;
            gap: 4px;
            flex-shrink: 0;
        }

        .btn-small {
            padding: 4px 8px;
            font-size: 11px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s ease;
            min-width: 50px;
        }

        .btn-small.btn-primary {
            background: #2c5aa0;
            color: white;
        }

        .btn-small.btn-primary:hover {
            background: #1e3f73;
        }

        .btn-small.btn-secondary {
            background: #f8f9fa;
            color: #666;
            border: 1px solid #e0e0e0;
        }

        .btn-small.btn-secondary:hover {
            background: #e9ecef;
        }

        .notification-footer {
            padding: 15px 20px;
            border-top: 1px solid #f0f0f0;
            text-align: center;
        }

        .view-all-notifications {
            background: none;
            border: none;
            color: #2c5aa0;
            font-size: 14px;
            cursor: pointer;
            padding: 8px 16px;
            border-radius: 6px;
            transition: background 0.2s ease;
            width: 100%;
        }

        .view-all-notifications:hover {
            background: rgba(44, 90, 160, 0.1);
        }

        .admin-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
            border: 2px solid #dc3545;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.4);
        }

        .admin-name {
            font-weight: 500;
            color: #e0e0e0;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 260px;
            margin-top: 70px;
            padding: 30px;
            transition: margin-left 0.3s ease;
            min-height: calc(100vh - 70px);
        }

        .main-content.full-width {
            margin-left: 0;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 300;
            color: #e0e0e0;
            margin-bottom: 30px;
            text-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(220, 53, 69, 0.1);
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(220, 53, 69, 0.3);
            border-color: #dc3545;
        }

        .stat-card.users {
            border-left-color: #dc3545;
        }

        .stat-card.courses {
            border-left-color: #dc3545;
        }

        .stat-card.nfts {
            border-left-color: #dc3545;
        }

        .stat-card.completion {
            border-left-color: #dc3545;
        }

        .stat-card.pending {
            border-left-color: #dc3545;
        }

        .stat-card.approved {
            border-left-color: #dc3545;
        }

        .stat-card.rejected {
            border-left-color: #dc3545;
        }

        .stat-card.published {
            border-left-color: #dc3545;
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.users {
            background: #667eea;
        }

        .stat-icon.courses {
            background: #28a745;
        }

        .stat-icon.nfts {
            background: #ffc107;
        }

        .stat-icon.completion {
            background: #17a2b8;
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 600;
            color: #dc3545;
            margin-bottom: 5px;
            text-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
        }

        .stat-label {
            color: #aaa;
            font-size: 0.9rem;
        }

        .trend-indicator {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: #dc3545;
            margin-top: 5px;
        }

        /* Activity Table */
        .activity-section {
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(220, 53, 69, 0.1);
            overflow: hidden;
        }

        .section-header {
            padding: 25px 30px;
            border-bottom: 1px solid #444;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 500;
            color: #e0e0e0;
        }

        .activity-table {
            width: 100%;
            border-collapse: collapse;
        }

        .activity-table th,
        .activity-table td {
            padding: 15px 30px;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        .activity-table th {
            background: #3d3d3d;
            font-weight: 600;
            color: #dc3545;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .activity-table td {
            color: #aaa;
        }

        .activity-table tbody tr:hover {
            background: #3d3d3d;
        }

        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 500;
            font-size: 0.8rem;
            margin-right: 10px;
            border: 1px solid #dc3545;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .action-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .action-badge.login {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-badge.course {
            background: #d4edda;
            color: #155724;
        }

        .action-badge.nft {
            background: #fff3cd;
            color: #856404;
        }

        /* Pagination */
        .pagination {
            padding: 20px 30px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .page-btn {
            padding: 8px 12px;
            border: 1px solid #555;
            background: #2d2d2d;
            color: #dc3545;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .page-btn:hover,
        .page-btn.active {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.4);
        }

        .page-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Overlay for mobile */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .overlay.show {
            display: block;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-260px);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .header {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .hamburger {
                display: block;
            }

            .search-box {
                width: 200px;
            }

            .admin-name {
                display: none;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .activity-table {
                font-size: 0.9rem;
            }

            .activity-table th,
            .activity-table td {
                padding: 12px 15px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .main-content {
                padding: 20px 15px;
            }
        }

        @media (max-width: 480px) {
            .search-box {
                width: 150px;
            }

            .header {
                padding: 0 15px;
            }

            .header-right {
                gap: 10px;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
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

        /* Users Section Styles */
        .content-section {
            width: 100%;
        }

        .user-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 1px solid #444;
            padding-bottom: 0;
        }

        .tab-btn {
            padding: 12px 24px;
            background: none;
            border: none;
            color: #aaa;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .tab-btn:hover {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .tab-btn.active {
            color: #dc3545;
            border-bottom-color: #dc3545;
            background: rgba(220, 53, 69, 0.2);
        }

        .users-section {
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.1);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-select {
            padding: 8px 12px;
            border: 1px solid #555;
            border-radius: 6px;
            background: #3d3d3d;
            color: #e0e0e0;
            font-size: 14px;
            cursor: pointer;
        }

        .filter-select:focus {
            outline: none;
            border-color: #dc3545;
            box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .users-table th,
        .users-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }

        .users-table th {
            background: #3d3d3d;
            font-weight: 600;
            color: #dc3545;
            font-size: 14px;
        }

        .users-table td {
            font-size: 14px;
            color: #aaa;
        }

        .users-table tbody tr:hover {
            background: #3d3d3d;
        }

        .user-details {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: #e0e0e0;
            margin-bottom: 2px;
        }

        .user-email {
            font-size: 12px;
            color: #aaa;
        }

        .role-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .role-badge.creator {
            background: #e3f2fd;
            color: #1976d2;
        }

        .role-badge.learner {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.active {
            background: #e8f5e8;
            color: #2e7d32;
        }

        .status-badge.inactive {
            background: #fff3e0;
            color: #f57c00;
        }

        .status-badge.suspended {
            background: #ffebee;
            color: #d32f2f;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .btn-icon {
            background: none;
            border: none;
            padding: 6px;
            cursor: pointer;
            border-radius: 4px;
            transition: background 0.2s ease;
            font-size: 14px;
        }

        .btn-icon:hover {
            background: #f0f0f0;
        }

        .btn-icon.delete:hover {
            background: #ffebee;
        }

        .stat-icon.creators {
            background: linear-gradient(135deg, #e91e63 0%, #ad1457 100%);
        }

        .stat-icon.learners {
            background: linear-gradient(135deg, #9c27b0 0%, #6a1b9a 100%);
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
        }

        .stat-icon.new {
            background: linear-gradient(135deg, #ff9800 0%, #e65100 100%);
        }

        .activity-chart-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
            border: 2px dashed #ddd;
        }

        .chart-placeholder {
            text-align: center;
            color: #666;
        }

        .chart-placeholder p {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .chart-placeholder small {
            font-size: 14px;
            color: #999;
        }

        /* Responsive adjustments for users table */
        @media (max-width: 768px) {
            .users-table {
                font-size: 12px;
            }
            
            .users-table th,
            .users-table td {
                padding: 10px 8px;
            }
            
            .header-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .user-tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
        }

        /* Courses Section Styles */
        .course-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 20px;
            border-bottom: 1px solid #444;
            padding-bottom: 0;
            overflow-x: auto;
            justify-content: center;
        }

        .course-tabs .tab-btn {
            padding: 8px 16px;
            font-size: 12px;
            border-radius: 6px 6px 0 0;
        }

        .courses-section {
            background: #2d2d2d;
            border: 1px solid #444;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(220, 53, 69, 0.1);
            margin-bottom: 30px;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        #coursesGrid {
            display: block !important;
            width: 100%;
        }

        .course-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            position: relative;
            border: none;
            width: 100%;
            max-width: none;
        }

        .course-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.12);
        }

        .course-card:hover .course-image::before {
            opacity: 0.6;
        }

        .course-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            z-index: 5;
        }

        .course-image {
            position: relative;
            height: 120px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px 12px 0 0;
        }

        .course-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.1) 75%);
            background-size: 20px 20px;
        }

        /* Colorful gradient backgrounds for different courses */
        .course-card[data-status="pending"] .course-image {
            background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        }

        .course-card[data-status="approved"] .course-image {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }

        .course-card[data-status="rejected"] .course-image {
            background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
        }

        .course-card[data-status="published"] .course-image {
            background: linear-gradient(135deg, #a8caba 0%, #5d4e75 100%);
        }

        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .course-avatar {
            position: absolute;
            bottom: -18px;
            left: 50%;
            transform: translateX(-50%);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            font-weight: bold;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }
        }

        .course-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            color: #333;
        }

        .course-status.pending {
            background: rgba(255, 193, 7, 0.9);
            color: #856404;
        }

        .course-status.approved {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .course-status.rejected {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .course-status.published {
            background: rgba(23, 162, 184, 0.9);
            color: white;
        }

        .course-info {
            padding: 25px 16px 16px 16px;
            text-align: center;
            background: white;
        }

        .course-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 8px;
            line-height: 1.3;
            min-height: 2.6em;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .course-creator {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .course-rating {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 15px;
        }

        .star-rating {
            display: flex;
            gap: 2px;
        }

        .star {
            color: #ffc107;
            font-size: 14px;
        }

        .star.empty {
            color: #e9ecef;
        }

        .rating-text {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
        }

        .course-students {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .course-description {
            font-size: 13px;
            color: #6c757d;
            line-height: 1.5;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-align: center;
        }

        .course-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
            justify-content: center;
        }

        .course-meta span {
            font-size: 11px;
            color: #6c757d;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 12px;
            border: 1px solid #e9ecef;
        }

        .course-details {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 20px;
            line-height: 1.4;
            text-align: center;
        }

        .course-details p {
            margin-bottom: 5px;
        }

        .course-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            border: 1px solid #667eea;
        }

        .btn-primary:hover {
            background: #5a67d8;
            border-color: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: #28a745;
            color: white;
            border: 1px solid #28a745;
        }

        .btn-success:hover {
            background: #1e7e34;
            border-color: #1e7e34;
            box-shadow: 0 0 10px rgba(40, 167, 69, 0.4);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: 1px solid #dc3545;
        }

        .btn-danger:hover {
            background: #a71e2a;
            border-color: #a71e2a;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.4);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
            border: 1px solid #ffc107;
        }

        .btn-warning:hover {
            background: #e0a800;
            border-color: #e0a800;
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.4);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
            border: 1px solid #17a2b8;
        }

        .btn-info:hover {
            background: #138496;
            border-color: #138496;
            box-shadow: 0 0 10px rgba(23, 162, 184, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            border: 1px solid #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
            border-color: #545b62;
            box-shadow: 0 0 10px rgba(108, 117, 125, 0.4);
        }

        .stat-icon.pending {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.approved {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.rejected {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.published {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.users {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.courses {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.nfts {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.completion {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.creators {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.learners {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.active {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        .stat-icon.new {
            background: linear-gradient(135deg, #dc3545 0%, #a71e2a 100%);
        }

        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: #2d2d2d;
            border: 1px solid #dc3545;
            border-radius: 12px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 25px;
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-header h2 {
            margin: 0;
            color: #333;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
        }

        .modal-body {
            padding: 25px;
        }

        .modal-footer {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            padding: 20px 25px;
            border-top: 1px solid #e0e0e0;
        }

        .review-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }

        .review-tab {
            padding: 10px 20px;
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .review-tab:hover {
            color: #2c5aa0;
            background: rgba(44, 90, 160, 0.05);
        }

        .review-tab.active {
            color: #2c5aa0;
            border-bottom-color: #2c5aa0;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .course-overview p {
            margin-bottom: 10px;
            line-height: 1.5;
        }

        .content-checklist {
            margin-bottom: 20px;
        }

        .content-checklist h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .content-checklist label {
            display: block;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 14px;
        }

        .content-checklist input[type="checkbox"] {
            margin-right: 10px;
        }

        .rating-section {
            margin-bottom: 20px;
        }

        .rating-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .rating-stars {
            display: flex;
            gap: 5px;
        }

        .star {
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .star:hover {
            transform: scale(1.2);
        }

        .star.selected {
            color: #ffc107;
        }

        .feedback-section textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            resize: vertical;
        }

        .feedback-section label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        /* Responsive adjustments for courses */
        @media (max-width: 768px) {
            .courses-grid {
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 15px;
                padding: 15px;
            }
            
            .course-actions {
                justify-content: center;
            }
            
            .course-meta {
                justify-content: center;
            }
            
            .modal-content {
                margin: 10px;
                max-height: 95vh;
            }
            
            .course-tabs {
                overflow-x: auto;
                white-space: nowrap;
                gap: 5px;
            }
        }

        @media (max-width: 480px) {
            .courses-grid {
                grid-template-columns: 1fr;
                gap: 15px;
                padding: 10px;
            }
            
            .course-tabs .tab-btn {
                padding: 6px 12px;
                font-size: 11px;
            }
        }

        /* Analytics Section Styles */
        .analytics-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .time-selector {
            display: flex;
            gap: 10px;
        }

        .time-btn {
            padding: 8px 16px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .time-btn:hover {
            background: #f8f9fa;
            border-color: #2c5aa0;
        }

        .time-btn.active {
            background: #2c5aa0;
            color: white;
            border-color: #2c5aa0;
        }

        .export-controls {
            display: flex;
            gap: 10px;
        }

        .analytics-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .analytics-grid-secondary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .analytics-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .chart-controls select,
        .chart-filter {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            background: white;
        }

        .chart-container {
            height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chart-placeholder {
            text-align: center;
            color: #666;
            width: 100%;
        }

        .chart-placeholder.large {
            height: 100%;
        }

        .chart-mock {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
        }

        .chart-bars {
            display: flex;
            align-items: end;
            gap: 8px;
            margin-bottom: 20px;
            height: 120px;
        }

        .bar {
            width: 20px;
            background: linear-gradient(180deg, #2c5aa0 0%, #1e3f73 100%);
            border-radius: 4px 4px 0 0;
            animation: barGrow 1s ease-out;
        }

        @keyframes barGrow {
            from {
                height: 0;
            }
            to {
                height: var(--height);
            }
        }

        .chart-placeholder p {
            font-size: 16px;
            margin-bottom: 8px;
        }

        .chart-placeholder small {
            font-size: 12px;
            color: #999;
        }

        .demographics-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .demo-section h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .country-list,
        .device-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .country-item,
        .device-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
        }

        .flag,
        .device-icon {
            font-size: 16px;
            width: 20px;
        }

        .country-name,
        .device-name {
            flex: 1;
            font-size: 14px;
        }

        .country-percentage,
        .device-percentage {
            font-weight: 600;
            color: #2c5aa0;
            font-size: 14px;
        }

        .performance-content {
            display: flex;
            flex-direction: column;
        }

        .performance-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .metric-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .metric-icon {
            font-size: 24px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 8px;
        }

        .metric-details {
            display: flex;
            flex-direction: column;
        }

        .metric-value {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
        }

        .metric-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .realtime-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #28a745;
            font-size: 14px;
            font-weight: 500;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background: #28a745;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        .realtime-stats {
            display: flex;
            gap: 30px;
            margin-bottom: 20px;
        }

        .realtime-stat {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2c5aa0;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .realtime-feed h4 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .activity-feed {
            max-height: 200px;
            overflow-y: auto;
        }

        .feed-item {
            display: flex;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }

        .feed-time {
            color: #666;
            min-width: 50px;
        }

        .feed-action {
            flex: 1;
        }

        .feed-location {
            color: #999;
        }

        .no-data {
            text-align: center;
            color: #999;
            font-style: italic;
            margin-top: 20px;
        }

        .page-list,
        .source-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .page-item,
        .source-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .page-info,
        .source-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .source-info {
            flex-direction: row;
            align-items: center;
            gap: 10px;
        }

        .page-title,
        .source-name {
            font-weight: 600;
            color: #333;
        }

        .page-url {
            font-size: 12px;
            color: #666;
            font-family: monospace;
        }

        .page-views,
        .source-percentage {
            font-weight: 600;
            color: #2c5aa0;
        }

        .source-icon {
            font-size: 16px;
        }

        .analytics-table-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .table-filter {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
            font-size: 14px;
        }

        .analytics-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .analytics-table th,
        .analytics-table td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .analytics-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .analytics-table td {
            font-size: 14px;
        }

        .table-item {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .item-title {
            font-weight: 600;
            color: #333;
        }

        .item-url {
            font-size: 12px;
            color: #666;
            font-family: monospace;
        }

        .stat-icon.traffic {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        .stat-icon.pageviews {
            background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%);
        }

        .stat-icon.sessions {
            background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
        }

        .stat-icon.bounce {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
        }

        /* Responsive adjustments for analytics */
        @media (max-width: 1024px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .demographics-content {
                grid-template-columns: 1fr;
            }
            
            .performance-metrics {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .analytics-controls {
                flex-direction: column;
                align-items: stretch;
            }
            
            .time-selector {
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .analytics-grid-secondary {
                grid-template-columns: 1fr;
            }
            
            .realtime-stats {
                justify-content: center;
            }
            
            .table-controls {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
</head>
<body>
       <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>Learnity</h2>
            <p>Admin Panel</p>
        </div>
        
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a href="#" class="nav-link active" data-tab="dashboard">
                    <span class="nav-icon">📊</span>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-tab="users">
                    <span class="nav-icon">👥</span>
                    Users
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" data-tab="courses">
                    <span class="nav-icon">📚</span>
                    Manage Courses
                </a>
            </li>
            <li class="nav-item">
                <a href="login.html" class="nav-link">
                    <span class="nav-icon">🚪</span>
                    Logout
                </a>
            </li>
        </ul>
    </nav>

        <!-- Header -->
    <header class="header" id="header">
        <button class="hamburger" id="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <div class="header-right">
            <div class="admin-info">
                <div class="admin-avatar"><?php echo strtoupper(substr($admin['full_name'] ?? 'A', 0, 1)); ?></div>
                <span class="admin-name"><?php echo htmlspecialchars($admin['full_name'] ?? 'Administrator'); ?></span>
            </div>
        </div>
    </header>

    <!-- Overlay for mobile -->
    <div class="overlay" id="overlay"></div>

       <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Dashboard Content -->
        <div class="content-section" id="dashboardContent">
            <div class="page-title">
                <h1>Admin Dashboard</h1>
                <p>Monitor and manage your learning platform</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card users">
                    <div class="stat-header">
                        <div class="stat-icon users">👥</div>
                        <span class="trend-indicator">+<?php echo rand(5, 25); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-label">Total Users</div>
                </div>

                <div class="stat-card courses">
                    <div class="stat-header">
                        <div class="stat-icon courses">📚</div>
                        <span class="trend-indicator">+<?php echo rand(3, 18); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($active_courses); ?></div>
                    <div class="stat-label">Active Courses</div>
                </div>

                <div class="stat-card nfts">
                    <div class="stat-header">
                        <div class="stat-icon nfts">🏆</div>
                        <span class="trend-indicator">+<?php echo rand(8, 30); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($nft_certificates); ?></div>
                    <div class="stat-label">NFT Certificates</div>
                </div>
            </div>

            <!-- Activity Section -->
            <div class="activity-section">
                <div class="section-header">
                    <h2 class="section-title">Recent Activity</h2>
                </div>
                
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Course</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($recent_activities)): ?>
                            <?php foreach ($recent_activities as $activity): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($activity['user_name'], 0, 1) . substr(explode(' ', $activity['user_name'])[1] ?? '', 0, 1)); ?>
                                            </div>
                                            <span><?php echo htmlspecialchars($activity['user_name']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $badge_class = '';
                                        $action_text = '';
                                        switch ($activity['action_type']) {
                                            case 'enrollment':
                                                $badge_class = 'course';
                                                $action_text = 'Enrolled';
                                                break;
                                            case 'completion':
                                                $badge_class = 'nft';
                                                $action_text = 'Completed';
                                                break;
                                            case 'course_creation':
                                                $badge_class = 'login';
                                                $action_text = 'Created Course';
                                                break;
                                            default:
                                                $badge_class = 'login';
                                                $action_text = ucfirst($activity['action_description']);
                                        }
                                        ?>
                                        <span class="action-badge <?php echo $badge_class; ?>"><?php echo $action_text; ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($activity['item_name']); ?></td>
                                    <td>
                                        <?php 
                                        $time_diff = time() - strtotime($activity['created_at']);
                                        if ($time_diff < 60) {
                                            echo 'Just now';
                                        } elseif ($time_diff < 3600) {
                                            echo floor($time_diff / 60) . ' min ago';
                                        } elseif ($time_diff < 86400) {
                                            echo floor($time_diff / 3600) . ' hour' . (floor($time_diff / 3600) > 1 ? 's' : '') . ' ago';
                                        } else {
                                            echo floor($time_diff / 86400) . ' day' . (floor($time_diff / 86400) > 1 ? 's' : '') . ' ago';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; color: #666; padding: 40px;">
                                    <div style="display: flex; flex-direction: column; align-items: center; gap: 10px;">
                                        <i class="fas fa-clock" style="font-size: 2rem; opacity: 0.5;"></i>
                                        <p>No recent activity to display</p>
                                        <small>Activities will appear here as users interact with the platform</small>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <button class="page-btn" onclick="loadActivityPage(<?php echo $page - 1; ?>)">Previous</button>
                    <?php else: ?>
                        <button class="page-btn" disabled>Previous</button>
                    <?php endif; ?>
                    
                    <?php
                    // Show page numbers
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <button class="page-btn <?php echo ($i == $page) ? 'active' : ''; ?>" 
                                onclick="loadActivityPage(<?php echo $i; ?>)"><?php echo $i; ?></button>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <button class="page-btn" onclick="loadActivityPage(<?php echo $page + 1; ?>)">Next</button>
                    <?php else: ?>
                        <button class="page-btn" disabled>Next</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Users Content -->
        <div class="content-section" id="usersContent" style="display: none;">
            <div class="page-title">
                <h1>User Management</h1>
                <p>Manage creators, learners, and monitor their activities</p>
            </div>

            <!-- User Stats -->
            <div class="stats-grid">
                <div class="stat-card creators">
                    <div class="stat-header">
                        <div class="stat-icon creators">🎨</div>
                        <span class="trend-indicator">+<?php echo rand(3, 15); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_creators); ?></div>
                    <div class="stat-label">Total Creators</div>
                </div>

                <div class="stat-card learners">
                    <div class="stat-header">
                        <div class="stat-icon learners">📖</div>
                        <span class="trend-indicator">+<?php echo rand(5, 20); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($total_learners); ?></div>
                    <div class="stat-label">Total Learners</div>
                </div>

                <div class="stat-card active-users">
                    <div class="stat-header">
                        <div class="stat-icon active">🟢</div>
                        <span class="trend-indicator">+<?php echo rand(2, 12); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($active_today); ?></div>
                    <div class="stat-label">Active Today</div>
                </div>

                <div class="stat-card new-users">
                    <div class="stat-header">
                        <div class="stat-icon new">⭐</div>
                        <span class="trend-indicator">+<?php echo rand(1, 8); ?>%</span>
                    </div>
                    <div class="stat-value"><?php echo number_format($new_this_week); ?></div>
                    <div class="stat-label">New This Week</div>
                </div>
            </div>

            <!-- User Type Tabs -->
            <div class="user-tabs">
                <button class="tab-btn <?php echo ($user_filter == 'all') ? 'active' : ''; ?>" onclick="filterUsers('all')">All Users</button>
                <button class="tab-btn <?php echo ($user_filter == 'creators') ? 'active' : ''; ?>" onclick="filterUsers('creators')">Creators</button>
                <button class="tab-btn <?php echo ($user_filter == 'learners') ? 'active' : ''; ?>" onclick="filterUsers('learners')">Learners</button>
            </div>

            <!-- Users Table -->
            <div class="users-section">
                <div class="section-header">
                    <h2 class="section-title">User List</h2>
                    <div class="header-actions">
                        <div class="search-box">
                            <input type="text" placeholder="Search users..." id="userSearch">
                            <span class="search-icon">🔍</span>
                        </div>
                        <select class="filter-select">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>
                
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role</th>
                            <th>Courses</th>
                            <th>Join Date</th>
                            <th>Last Activity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <?php foreach ($users_list as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar"><?php echo strtoupper(substr($user['full_name'], 0, 2)); ?></div>
                                    <div class="user-details">
                                        <span class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></span>
                                        <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                </div>
                            </td>
                            <td><span class="role-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td>
                                <?php 
                                if ($user['role'] == 'creator') {
                                    echo $user['course_count'] . ' Created';
                                } else {
                                    echo $user['course_count'] . ' Enrolled';
                                }
                                ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php 
                                if ($user['last_activity']) {
                                    $last_activity = strtotime($user['last_activity']);
                                    $time_diff = time() - $last_activity;
                                    
                                    if ($time_diff < 3600) {
                                        echo floor($time_diff / 60) . ' min ago';
                                    } elseif ($time_diff < 86400) {
                                        echo floor($time_diff / 3600) . ' hours ago';
                                    } else {
                                        echo floor($time_diff / 86400) . ' days ago';
                                    }
                                } else {
                                    echo 'No activity';
                                }
                                ?>
                            </td>
                            <td><span class="status-badge active">Active</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon view" title="View Profile" onclick="viewUser(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">👁️</button>
                                    <button class="btn-icon edit" title="Edit User" onclick="editUser(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">✏️</button>
                                    <button class="btn-icon delete" title="Suspend User" onclick="suspendUser(<?php echo $user['id']; ?>, '<?php echo $user['role']; ?>')">🚫</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Users Pagination -->
                <div class="pagination">
                    <?php if ($user_page > 1): ?>
                        <button class="page-btn" onclick="loadUsersPageWithFilter(<?php echo $user_page - 1; ?>, '<?php echo $user_filter; ?>')">« Previous</button>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_user_pages; $i++): ?>
                        <button class="page-btn <?php echo ($i == $user_page) ? 'active' : ''; ?>" onclick="loadUsersPageWithFilter(<?php echo $i; ?>, '<?php echo $user_filter; ?>')"><?php echo $i; ?></button>
                    <?php endfor; ?>
                    
                    <?php if ($user_page < $total_user_pages): ?>
                        <button class="page-btn" onclick="loadUsersPageWithFilter(<?php echo $user_page + 1; ?>, '<?php echo $user_filter; ?>')">Next »</button>
                    <?php endif; ?>
                </div>
            </div>

             </div>

        </div>

        <!-- Manage Courses Content -->
        <div class="content-section" id="coursesContent" style="display: none;">
            <div class="page-title">
                <h1>Manage Courses</h1>
                <p>Review, approve, and manage courses from creators</p>
            </div>

            <!-- Quick Approval Actions Section (Moved to Top) -->
            <div class="courses-section">
                <!-- Course Filter Tabs (Moved to Top of Slider) -->
                <div class="course-tabs">
                    <button class="tab-btn" data-course-tab="pending">Pending Approval</button>
                    <button class="tab-btn" data-course-tab="approved">Approved</button>
                    <button class="tab-btn" data-course-tab="rejected">Rejected</button>
                    <button class="tab-btn active" data-course-tab="all">All Courses</button>
                </div>

                <div class="section-header">
                    <h3 class="section-title">Course Management Actions</h3>
                </div>

                <div class="courses-grid" id="coursesGrid">
                    <?php
                    // Get courses for approval
                    $courses_query = "
                        SELECT c.*, cr.full_name as creator_name, cr.email as creator_email
                        FROM courses c
                        JOIN creators cr ON c.creator_id = cr.id
                        ORDER BY c.created_at DESC
                    ";
                    $courses_result = mysqli_query($conn, $courses_query);
                    $all_courses = [];

                    if (mysqli_num_rows($courses_result) > 0):
                        while ($course = mysqli_fetch_assoc($courses_result)) {
                            $all_courses[] = $course;
                        }
                        
                        // Debug: Show how many courses were found
                        echo "<!-- DEBUG: Found " . count($all_courses) . " courses -->";
                        
                        foreach ($all_courses as $course):
                    ?>
                    <div class="course-card" data-status="<?php echo $course['status']; ?>">
                        <div class="course-image">
                            <?php if (!empty($course['thumbnail'])): ?>
                                <img src="<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="Course Thumbnail">
                            <?php else: ?>
                                <div style="height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">
                                    📚
                                </div>
                            <?php endif; ?>
                            <div class="course-status <?php echo $course['status']; ?>">
                                <?php echo ucfirst($course['status']); ?>
                            </div>
                        </div>
                        
                        <div class="course-info">
                            <h3 class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            <p class="course-creator">by <?php echo htmlspecialchars($course['creator_name']); ?></p>
                            <p class="course-description"><?php echo htmlspecialchars(substr($course['description'], 0, 120)) . '...'; ?></p>
                            
                            <div class="course-meta">
                                <span>📅 <?php echo date('M j, Y', strtotime($course['created_at'])); ?></span>
                                <span>⏱️ <?php echo $course['duration']; ?></span>
                                <span>🏷️ <?php echo htmlspecialchars($course['category']); ?></span>
                            </div>

                            <div class="course-details">
                                <p><strong>Creator Email:</strong> <?php echo htmlspecialchars($course['creator_email']); ?></p>
                                <p><strong>NFT Rewards:</strong> <?php echo $course['nft_reward'] ? 'Yes' : 'No'; ?></p>
                                <p><strong>Difficulty:</strong> <?php echo ucfirst($course['difficulty']); ?></p>
                            </div>

                            <div class="course-actions">
                                <?php if ($course['status'] == 'pending'): ?>
                                    <button class="btn btn-success" onclick="approveCourse(<?php echo $course['id']; ?>)">
                                        ✅ Approve
                                    </button>
                                    <button class="btn btn-danger" onclick="rejectCourse(<?php echo $course['id']; ?>)">
                                        ❌ Reject
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-info" onclick="viewCourseDetails(<?php echo $course['id']; ?>)">
                                    👁️ View Details
                                </button>
                                
                                <?php if ($course['status'] == 'published'): ?>
                                    <button class="btn btn-warning" onclick="suspendCourse(<?php echo $course['id']; ?>)">
                                        ⏸️ Suspend
                                    </button>
                                <?php endif; ?>
                                
                                <button class="btn btn-secondary" onclick="deleteCourse(<?php echo $course['id']; ?>)">
                                    🗑️ Delete
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php 
                        endforeach;
                    else:
                    ?>
                    <!-- DEBUG: No courses found in database -->
                    <div class="no-data">
                        <p>No courses found.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Course Stats (Moved to Bottom) -->
            <div class="stats-grid">
                <div class="stat-card pending">
                    <div class="stat-header">
                        <div class="stat-icon pending">⏳</div>
                    </div>
                    <div class="stat-value">
                        <?php
                        $pending_courses_query = "SELECT COUNT(*) as count FROM courses WHERE status = 'pending'";
                        $pending_courses_result = mysqli_query($conn, $pending_courses_query);
                        $pending_courses = mysqli_fetch_assoc($pending_courses_result)['count'];
                        echo $pending_courses;
                        ?>
                    </div>
                    <div class="stat-label">Pending Approval</div>
                </div>

                <div class="stat-card approved">
                    <div class="stat-header">
                        <div class="stat-icon approved">✅</div>
                    </div>
                    <div class="stat-value">
                        <?php
                        $approved_courses_query = "SELECT COUNT(*) as count FROM courses WHERE status = 'published'";
                        $approved_courses_result = mysqli_query($conn, $approved_courses_query);
                        $approved_courses = mysqli_fetch_assoc($approved_courses_result)['count'];
                        echo $approved_courses;
                        ?>
                    </div>
                    <div class="stat-label">Approved Courses</div>
                </div>

                <div class="stat-card rejected">
                    <div class="stat-header">
                        <div class="stat-icon rejected">❌</div>
                    </div>
                    <div class="stat-value">
                        <?php
                        $rejected_courses_query = "SELECT COUNT(*) as count FROM courses WHERE status = 'rejected'";
                        $rejected_courses_result = mysqli_query($conn, $rejected_courses_query);
                        $rejected_courses = mysqli_fetch_assoc($rejected_courses_result)['count'];
                        echo $rejected_courses;
                        ?>
                    </div>
                    <div class="stat-label">Rejected Courses</div>
                </div>

                <div class="stat-card published">
                    <div class="stat-header">
                        <div class="stat-icon published">🚀</div>
                    </div>
                    <div class="stat-value">
                        <?php
                        $total_courses_query = "SELECT COUNT(*) as count FROM courses";
                        $total_courses_result = mysqli_query($conn, $total_courses_query);
                        $total_courses = mysqli_fetch_assoc($total_courses_result)['count'];
                        echo $total_courses;
                        ?>
                    </div>
                    <div class="stat-label">Total Courses</div>
                </div>
            </div>
        </div>
    </main>

       <script>
        // Mobile menu toggle
        const hamburger = document.getElementById('hamburger');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');
        const mainContent = document.getElementById('mainContent');
        const header = document.getElementById('header');

        hamburger.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
            overlay.classList.toggle('show');
            mainContent.classList.toggle('full-width');
            header.classList.toggle('full-width');
        });

        // Close sidebar when clicking overlay
        overlay.addEventListener('click', () => {
            sidebar.classList.add('collapsed');
            overlay.classList.remove('show');
            mainContent.classList.add('full-width');
            header.classList.add('full-width');
        });

        // Close sidebar on mobile when clicking a nav link
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    overlay.classList.remove('show');
                    mainContent.classList.add('full-width');
                    header.classList.add('full-width');
                }
            });
        });

        // Handle responsive behavior
        function handleResize() {
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('full-width');
                header.classList.add('full-width');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('full-width');
                header.classList.remove('full-width');
                overlay.classList.remove('show');
            }
        }

        window.addEventListener('resize', handleResize);
        window.addEventListener('load', handleResize);

        // Animate stats on load
        function animateStats() {
            const statValues = document.querySelectorAll('#dashboardContent .stat-value');
            // Use actual PHP values instead of hardcoded zeros
            const values = [<?php echo $total_users; ?>, <?php echo $active_courses; ?>, <?php echo $nft_certificates; ?>];
            
            statValues.forEach((stat, index) => {
                if (index >= values.length) return; // Skip if no value for this stat
                
                let current = 0;
                const target = values[index];
                const increment = target / 50; // Faster animation
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    stat.textContent = Math.floor(current).toLocaleString();
                }, 30);
            });
        }

        // Animate user stats when users tab is opened
        function animateUserStats() {
            const userStatValues = document.querySelectorAll('#usersContent .stat-value');
            // Use actual PHP values for user statistics
            const userValues = [<?php echo $total_creators; ?>, <?php echo $total_learners; ?>, <?php echo $active_today; ?>, <?php echo $new_this_week; ?>];
            
            userStatValues.forEach((stat, index) => {
                if (index >= userValues.length) return; // Skip if no value for this stat
                
                let current = 0;
                const target = userValues[index];
                const increment = target / 40; // Faster animation
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        current = target;
                        clearInterval(timer);
                    }
                    
                    stat.textContent = Math.floor(current).toLocaleString();
                }, 25);
            });
        }

        // Activity pagination function
        function loadActivityPage(page) {
            // Update URL with new page parameter
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('page', page);
            
            // Reload the page with new page parameter
            window.location.href = currentUrl.toString();
        }

        // Users pagination function
        function loadUsersPage(page) {
            // Update URL with new user_page parameter
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('user_page', page);
            
            // Reload the page with new page parameter
            window.location.href = currentUrl.toString();
        }

        // User filtering function
        function filterUsers(filter) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('user_filter', filter);
            currentUrl.searchParams.delete('user_page'); // Reset to page 1 when filtering
            currentUrl.hash = '#users'; // Maintain users tab state
            
            window.location.href = currentUrl.toString();
        }

        // Users pagination with filter preservation
        function loadUsersPageWithFilter(page, filter) {
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('user_page', page);
            currentUrl.searchParams.set('user_filter', filter);
            currentUrl.hash = '#users'; // Maintain users tab state
            
            window.location.href = currentUrl.toString();
        }

        // User action functions
        function viewUser(userId, role) {
            alert(`Viewing ${role} profile (ID: ${userId})`);
            // TODO: Implement user profile view
        }

        function editUser(userId, role) {
            alert(`Editing ${role} (ID: ${userId})`);
            // TODO: Implement user edit functionality
        }

        function suspendUser(userId, role) {
            if (confirm(`Are you sure you want to suspend this ${role}?`)) {
                alert(`${role} suspended (ID: ${userId})`);
                // TODO: Implement user suspension
            }
        }

        // Initialize animations
        window.addEventListener('load', () => {
            setTimeout(animateStats, 500);
            
            // Check URL hash to determine which tab to show
            const hash = window.location.hash.substring(1); // Remove # from hash
            if (hash && ['dashboard', 'users'].includes(hash)) {
                showContent(hash);
            } else {
                // Default to dashboard if no valid hash
                showContent('dashboard');
            }
        });

        // Tab switching functionality
        function showContent(tabName) {
            // Hide all content sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.style.display = 'none';
            });
            
            // Show selected content section
            const targetContent = document.getElementById(tabName + 'Content');
            if (targetContent) {
                targetContent.style.display = 'block';
                
                // Update URL hash without reloading page
                if (window.location.hash !== '#' + tabName) {
                    history.replaceState(null, null, '#' + tabName);
                }
                
                // If showing users content, reinitialize user tabs and search
                if (tabName === 'users') {
                    setTimeout(() => {
                        initUserTabs();
                        initUserSearch();
                        animateUserStats(); // Animate user statistics
                    }, 100);
                }
                


            }
            
            // Update active nav link
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            const activeLink = document.querySelector(`[data-tab="${tabName}"]`);
            if (activeLink) {
                activeLink.classList.add('active');
            }
        }

        // Add click event listeners to nav links with data-tab attribute
        document.querySelectorAll('[data-tab]').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const tabName = link.getAttribute('data-tab');
                showContent(tabName);
                
                // Close mobile menu if open
                if (window.innerWidth <= 768) {
                    sidebar.classList.add('collapsed');
                    overlay.classList.remove('show');
                    mainContent.classList.add('full-width');
                    header.classList.add('full-width');
                }
            });
        });

        // Notification dropdown functionality
        function initNotificationDropdown() {
            const notificationIcon = document.querySelector('.notification');
            const notificationDropdown = document.querySelector('.notification-dropdown');
            
            if (!notificationIcon || !notificationDropdown) return;
            
            // Toggle dropdown when notification icon is clicked
            notificationIcon.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationDropdown.classList.toggle('show');
                
                // Update notification badge if dropdown is opened
                if (notificationDropdown.classList.contains('show')) {
                    updateNotificationBadge();
                }
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
                    notificationDropdown.classList.remove('show');
                }
            });
            
            // Mark all as read functionality
            const markAllReadBtn = notificationDropdown.querySelector('.mark-all-read');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', () => {
                    markAllNotificationsAsRead();
                });
            }
            
            // Individual notification interactions
            const notificationItems = notificationDropdown.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                // Mark as read when clicked
                item.addEventListener('click', () => {
                    markNotificationAsRead(item);
                });
                
                // Handle action buttons
                const actionBtns = item.querySelectorAll('.btn-small');
                actionBtns.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        handleNotificationAction(btn, item);
                    });
                });
            });
        }
        
        function updateNotificationBadge() {
            const badge = document.querySelector('.notification-badge');
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            
            if (badge) {
                if (unreadItems.length > 0) {
                    badge.style.display = 'block';
                } else {
                    badge.style.display = 'none';
                }
            }
        }
        
        function markNotificationAsRead(item) {
            item.classList.remove('unread');
            item.classList.add('read');
            updateNotificationBadge();
        }
        
        function markAllNotificationsAsRead() {
            const unreadItems = document.querySelectorAll('.notification-item.unread');
            unreadItems.forEach(item => {
                item.classList.remove('unread');
                item.classList.add('read');
            });
            updateNotificationBadge();
        }
        
        function handleNotificationAction(button, notificationItem) {
            const action = button.textContent.toLowerCase().trim();
            
            if (action === 'approve' || action === 'review') {
                // Handle approval/review actions
                console.log('Handling notification action:', action);
                
                // Mark as read and potentially remove the notification
                markNotificationAsRead(notificationItem);
                
                // You can add specific logic here for different notification types
                // For example, opening relevant modals or redirecting to specific content
            }
        }

        // User tab functionality
        function initUserTabs() {
            // The user tabs are already handled by onclick events in HTML
            // This function can be used for additional initialization if needed
            console.log('User tabs initialized');
        }

        // Search functionality for users
        function initUserSearch() {
            const searchInput = document.getElementById('userSearch');
            
            if (searchInput) {
                // Remove existing event listeners by cloning the element
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);
                
                newSearchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    const tableRows = document.querySelectorAll('#usersTableBody tr');
                    
                    tableRows.forEach(row => {
                        const userName = row.querySelector('.user-name');
                        const userEmail = row.querySelector('.user-email');
                        
                        if (userName && userEmail) {
                            const nameText = userName.textContent.toLowerCase();
                            const emailText = userEmail.textContent.toLowerCase();
                            
                            if (nameText.includes(searchTerm) || emailText.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        }
                    });
                });
            }
        }

        // Initialize user management features when the page loads
        document.addEventListener('DOMContentLoaded', () => {
            initUserTabs();
            initUserSearch();
            initCourseTabs();
            
            // Debug: Check how many course cards exist
            const courseCards = document.querySelectorAll('.course-card');
            console.log('DEBUG: Found', courseCards.length, 'course cards on page load');
            
            // Show all courses by default
            filterCourses('all');
        });

        // Course Management Functions
        function initCourseTabs() {
            const courseTabBtns = document.querySelectorAll('[data-course-tab]');
            
            courseTabBtns.forEach((btn) => {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    
                    document.querySelectorAll('[data-course-tab]').forEach(b => b.classList.remove('active'));
                    newBtn.classList.add('active');
                    
                    const tabType = newBtn.getAttribute('data-course-tab');
                    filterCourses(tabType);
                });
            });
        }

        function filterCourses(status) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                
                if (status === 'all' || cardStatus === status) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function initCourseSearch() {
            const searchInput = document.getElementById('courseSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            
            if (searchInput) {
                const newSearchInput = searchInput.cloneNode(true);
                searchInput.parentNode.replaceChild(newSearchInput, searchInput);
                
                newSearchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value.toLowerCase();
                    filterCoursesBySearch(searchTerm);
                });
            }
            
            if (categoryFilter) {
                const newCategoryFilter = categoryFilter.cloneNode(true);
                categoryFilter.parentNode.replaceChild(newCategoryFilter, categoryFilter);
                
                newCategoryFilter.addEventListener('change', (e) => {
                    const category = e.target.value;
                    filterCoursesByCategory(category);
                });
            }
        }

        function filterCoursesBySearch(searchTerm) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const title = card.querySelector('.course-title').textContent.toLowerCase();
                const creator = card.querySelector('.course-creator').textContent.toLowerCase();
                const description = card.querySelector('.course-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || creator.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterCoursesByCategory(category) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const cardCategory = card.getAttribute('data-category');
                
                if (category === 'all' || cardCategory === category) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Course Action Functions
        function reviewCourse(courseId) {
            const modal = document.getElementById('courseReviewModal');
            modal.style.display = 'flex';
            
            // Populate modal with course data
            // This would typically fetch data from a database
            document.getElementById('reviewCourseTitle').textContent = 'Advanced React Development';
            document.getElementById('reviewCreator').textContent = 'John Smith';
            document.getElementById('reviewCategory').textContent = 'Web Development';
            document.getElementById('reviewDuration').textContent = '8 hours';
            document.getElementById('reviewPrice').textContent = '$129.99';
            document.getElementById('reviewDescription').textContent = 'Learn advanced React concepts including hooks, context, and performance optimization techniques.';
            
            // Store current course ID for approval/rejection
            modal.setAttribute('data-course-id', courseId);
            
            // Initialize review modal tabs
            initReviewTabs();
        }

        function initReviewTabs() {
            const reviewTabs = document.querySelectorAll('.review-tab');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            reviewTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    reviewTabs.forEach(t => t.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));
                    
                    tab.classList.add('active');
                    const tabName = tab.getAttribute('data-review-tab');
                    document.getElementById(tabName + 'Tab').classList.add('active');
                });
            });
            
            // Initialize star rating
            initStarRating();
        }

        function initStarRating() {
            const stars = document.querySelectorAll('.star');
            let selectedRating = 0;
            
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    selectedRating = index + 1;
                    updateStars(selectedRating);
                });
                
                star.addEventListener('mouseover', () => {
                    updateStars(index + 1);
                });
            });
            
            document.querySelector('.rating-stars').addEventListener('mouseleave', () => {
                updateStars(selectedRating);
            });
            
            function updateStars(rating) {
                stars.forEach((star, index) => {
                    if (index < rating) {
                        star.classList.add('selected');
                    } else {
                        star.classList.remove('selected');
                    }
                });
            }
        }

        function closeReviewModal() {
            document.getElementById('courseReviewModal').style.display = 'none';
        }

        function approveCourse(courseId) {
            if (confirm('Are you sure you want to approve this course?')) {
                // Update course status to approved
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Approved';
                statusElement.className = 'course-status approved';
                courseCard.setAttribute('data-status', 'approved');
                
                // Update actions
                updateCourseActions(courseCard, 'approved', courseId);
                
                alert('Course approved successfully!');
            }
        }

        function approveCourseFromModal() {
            const modal = document.getElementById('courseReviewModal');
            const courseId = modal.getAttribute('data-course-id');
            closeReviewModal();
            approveCourse(courseId);
        }

        function rejectCourse(courseId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                // Update course status to rejected
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Rejected';
                statusElement.className = 'course-status rejected';
                courseCard.setAttribute('data-status', 'rejected');
                
                // Update course details
                const detailsElement = courseCard.querySelector('.course-details');
                detailsElement.innerHTML = `
                    <p><strong>Rejected:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Reason:</strong> ${reason}</p>
                `;
                
                // Update actions
                updateCourseActions(courseCard, 'rejected', courseId);
                
                alert('Course rejected successfully!');
            }
        }

        function rejectCourseFromModal() {
            const feedback = document.getElementById('adminFeedback').value;
            const modal = document.getElementById('courseReviewModal');
            const courseId = modal.getAttribute('data-course-id');
            
            if (feedback.trim()) {
                closeReviewModal();
                
                // Update course status to rejected with feedback
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Rejected';
                statusElement.className = 'course-status rejected';
                courseCard.setAttribute('data-status', 'rejected');
                
                // Update course details
                const detailsElement = courseCard.querySelector('.course-details');
                detailsElement.innerHTML = `
                    <p><strong>Rejected:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Reason:</strong> ${feedback}</p>
                `;
                
                // Update actions
                updateCourseActions(courseCard, 'rejected', courseId);
                
                alert('Course rejected with feedback!');
            } else {
                alert('Please provide feedback before rejecting the course.');
            }
        }

        function publishCourse(courseId) {
            if (confirm('Are you sure you want to publish this course to the platform?')) {
                // Update course status to published
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Published';
                statusElement.className = 'course-status published';
                courseCard.setAttribute('data-status', 'published');
                
                // Update course details
                const detailsElement = courseCard.querySelector('.course-details');
                detailsElement.innerHTML = `
                    <p><strong>Published:</strong> ${new Date().toLocaleDateString()}</p>
                    <p><strong>Enrollments:</strong> 0 students</p>
                `;
                
                // Update actions
                updateCourseActions(courseCard, 'published', courseId);
                
                alert('Course published successfully!');
            }
        }

        function unpublishCourse(courseId) {
            if (confirm('Are you sure you want to unpublish this course?')) {
                // Update course status back to approved
                const courseCard = document.querySelector(`[onclick*="${courseId}"]`).closest('.course-card');
                const statusElement = courseCard.querySelector('.course-status');
                statusElement.textContent = 'Approved';
                statusElement.className = 'course-status approved';
                courseCard.setAttribute('data-status', 'approved');
                
                // Update actions
                updateCourseActions(courseCard, 'approved', courseId);
                
                alert('Course unpublished successfully!');
            }
        }

        function updateCourseActions(courseCard, status, courseId) {
            const actionsElement = courseCard.querySelector('.course-actions');
            
            let actionsHTML = '';
            
            switch(status) {
                case 'pending':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="reviewCourse('${courseId}')">📝 Review</button>
                        <button class="btn btn-success" onclick="approveCourse('${courseId}')">✅ Approve</button>
                        <button class="btn btn-danger" onclick="rejectCourse('${courseId}')">❌ Reject</button>
                    `;
                    break;
                case 'approved':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="reviewCourse('${courseId}')">👁️ View</button>
                        <button class="btn btn-success" onclick="publishCourse('${courseId}')">🌟 Publish</button>
                        <button class="btn btn-warning" onclick="editCourse('${courseId}')">✏️ Edit</button>
                    `;
                    break;
                case 'published':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="viewCourseStats('${courseId}')">📊 Analytics</button>
                        <button class="btn btn-warning" onclick="unpublishCourse('${courseId}')">📥 Unpublish</button>
                        <button class="btn btn-secondary" onclick="editCourse('${courseId}')">✏️ Edit</button>
                    `;
                    break;
                case 'rejected':
                    actionsHTML = `
                        <button class="btn btn-primary" onclick="reviewCourse('${courseId}')">📝 Re-review</button>
                        <button class="btn btn-info" onclick="provideFeedback('${courseId}')">💬 Feedback</button>
                        <button class="btn btn-danger" onclick="deleteCourse('${courseId}')">🗑️ Delete</button>
                    `;
                    break;
            }
            
            actionsElement.innerHTML = actionsHTML;
        }

        function editCourse(courseId) {
            alert('Edit course functionality - would open course editor');
        }

        function viewCourseStats(courseId) {
            alert('Course analytics functionality - would show detailed stats');
        }

        function provideFeedback(courseId) {
            const feedback = prompt('Provide additional feedback for the course creator:');
            if (feedback) {
                alert('Feedback sent to course creator!');
            }
        }

        // Close modal when clicking outside
        document.addEventListener('click', (e) => {
            const modal = document.getElementById('courseReviewModal');
            if (e.target === modal) {
                closeReviewModal();
            }
        });

        // Analytics Functions
        function initAnalytics() {
            initTimePeriodSelector();
            initAnalyticsCharts();
            initAnalyticsTable();
            startRealTimeUpdates();
        }

        function initTimePeriodSelector() {
            const timeBtns = document.querySelectorAll('.time-btn');
            
            timeBtns.forEach(btn => {
                // Remove existing event listeners
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);
                
                newBtn.addEventListener('click', () => {
                    document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                    newBtn.classList.add('active');
                    
                    const period = newBtn.getAttribute('data-period');
                    updateAnalyticsData(period);
                });
            });
        }

        function updateAnalyticsData(period) {
            // This would typically fetch real data from your analytics API
            console.log(`Updating analytics data for ${period} days`);
            
            // Simulate data update with animation
            animateAnalyticsStats();
            updateCharts(period);
        }

        function animateAnalyticsStats() {
            // This would be populated with real data from your backend
            const mockData = {
                visitors: 0,
                pageviews: 0,
                sessionDuration: '0:00',
                bounceRate: '0%'
            };
            
            // Update stat values with animation
            const statValues = document.querySelectorAll('#analyticsContent .stat-value');
            statValues.forEach((stat, index) => {
                // Animation logic would go here for real data
                stat.textContent = Object.values(mockData)[index];
            });
        }

        function initAnalyticsCharts() {
            // Initialize chart filters
            const chartFilters = document.querySelectorAll('.chart-filter');
            chartFilters.forEach(filter => {
                filter.addEventListener('change', (e) => {
                    const chartType = e.target.value;
                    updateChart(chartType);
                });
            });
        }

        function updateChart(chartType) {
            console.log(`Updating chart to show: ${chartType}`);
            // This would update the chart visualization
            // In a real implementation, you'd integrate with Chart.js, D3.js, etc.
        }

        function updateCharts(period) {
            // Update all charts based on time period
            console.log(`Updating all charts for ${period} days`);
            
            // Animate the mock chart bars
            const bars = document.querySelectorAll('.bar');
            bars.forEach((bar, index) => {
                // Reset animation
                bar.style.animation = 'none';
                bar.offsetHeight; // Trigger reflow
                bar.style.animation = 'barGrow 1s ease-out';
            });
        }

        function initAnalyticsTable() {
            const tableFilter = document.querySelector('.table-filter');
            if (tableFilter) {
                tableFilter.addEventListener('change', (e) => {
                    const filterType = e.target.value;
                    updateAnalyticsTable(filterType);
                });
            }
        }

        function updateAnalyticsTable(filterType) {
            console.log(`Filtering analytics table by: ${filterType}`);
            // This would update the table data based on the filter
            // In a real implementation, you'd fetch and populate with real data
        }

        function startRealTimeUpdates() {
            // Simulate real-time updates
            setInterval(() => {
                updateRealTimeData();
            }, 30000); // Update every 30 seconds
        }

        function updateRealTimeData() {
            // This would fetch real-time data from your analytics service
            const realTimeStats = document.querySelectorAll('.realtime-stat .stat-number');
            const activityFeed = document.querySelector('.activity-feed');
            
            // In a real implementation, you'd update with actual data
            // For now, we'll keep it at 0 as placeholder
            
            // Update timestamp for last update
            const now = new Date();
            const timeString = now.toLocaleTimeString();
            
            // Could add a "last updated" indicator
            console.log(`Real-time data updated at ${timeString}`);
        }

        // Export functionality
        function exportAnalyticsReport() {
            alert('Export functionality - would generate and download analytics report');
        }

        function scheduleReport() {
            alert('Schedule report functionality - would set up automated reports');
        }

        // Add event listeners for export buttons
        document.addEventListener('DOMContentLoaded', () => {
            // Export report buttons
            const exportBtns = document.querySelectorAll('.export-controls .btn');
            exportBtns.forEach(btn => {
                if (btn.textContent.includes('Export')) {
                    btn.addEventListener('click', exportAnalyticsReport);
                } else if (btn.textContent.includes('Schedule')) {
                    btn.addEventListener('click', scheduleReport);
                }
            });
            
            // Table export button
            const tableExportBtn = document.querySelector('.table-controls .btn');
            if (tableExportBtn) {
                tableExportBtn.addEventListener('click', exportAnalyticsReport);
            }

            // Course approval tab functionality
            const courseTabBtns = document.querySelectorAll('[data-course-tab]');
            courseTabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active class from all tabs
                    courseTabBtns.forEach(tab => tab.classList.remove('active'));
                    // Add active class to clicked tab
                    btn.classList.add('active');
                    
                    const status = btn.getAttribute('data-course-tab');
                    filterCourses(status);
                });
            });
        });

        // Course approval functions
        function approveCourse(courseId) {
            if (confirm('Are you sure you want to approve this course?')) {
                fetch('approve_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        action: 'approve'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Course approved successfully!');
                        location.reload(); // Refresh to show updated status
                    } else {
                        alert('Error approving course: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while approving the course');
                });
            }
        }

        function rejectCourse(courseId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason && reason.trim()) {
                fetch('approve_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        action: 'reject',
                        reason: reason
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Course rejected successfully!');
                        location.reload(); // Refresh to show updated status
                    } else {
                        alert('Error rejecting course: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while rejecting the course');
                });
            }
        }

        function suspendCourse(courseId) {
            if (confirm('Are you sure you want to suspend this course?')) {
                fetch('approve_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        action: 'suspend'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Course suspended successfully!');
                        location.reload();
                    } else {
                        alert('Error suspending course: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while suspending the course');
                });
            }
        }

        // Initialize course tabs and filtering
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize course tabs
            initCourseTabs();
            
            // Show all courses by default
            filterCourses('all');
            
            console.log('JS Debug: Course management initialized');
        });

        function initCourseTabs() {
            const tabButtons = document.querySelectorAll('[data-course-tab]');
            
            tabButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const status = this.getAttribute('data-course-tab');
                    filterCourses(status);
                });
            });
        }

        function filterCourses(status) {
            const courseCards = document.querySelectorAll('#coursesGrid .course-card');
            let visibleCount = 0;
            
            console.log(`JS Debug: filterCourses called with status: ${status}`);
            console.log(`JS Debug: Found ${courseCards.length} course cards`);
            
            // Filter courses
            courseCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                const shouldShow = status === 'all' || status === '' || cardStatus === status;
                
                if (shouldShow) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });
            
            // Update tab visual feedback
            const activeTab = document.querySelector(`[data-course-tab="${status}"]`);
            if (activeTab) {
                document.querySelectorAll('[data-course-tab]').forEach(tab => tab.classList.remove('active'));
                activeTab.classList.add('active');
            }
            
            console.log(`JS Debug: Filtered courses: ${visibleCount} visible for status "${status}"`);
        }

        function deleteCourse(courseId) {
            // Enhanced confirmation dialog
            const confirmMessage = `⚠️ WARNING: Are you sure you want to permanently delete this course?

This action will:
• Remove the course from the platform
• Delete all associated files and videos
• Remove course data permanently
• This action CANNOT be undone

Type 'DELETE' to confirm:`;
            
            const confirmation = prompt(confirmMessage);
            
            if (confirmation === 'DELETE') {
                // Show loading state
                const deleteBtn = document.querySelector(`button[onclick*="deleteCourse(${courseId})"]`);
                const originalText = deleteBtn.innerHTML;
                deleteBtn.innerHTML = '⏳ Deleting...';
                deleteBtn.disabled = true;
                
                fetch('approve_course.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        course_id: courseId,
                        action: 'delete'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        alert('✅ Course deleted successfully!');
                        
                        // Remove the course card with animation
                        const courseCard = deleteBtn.closest('.course-card');
                        if (courseCard) {
                            courseCard.style.transition = 'all 0.3s ease';
                            courseCard.style.transform = 'scale(0.8)';
                            courseCard.style.opacity = '0';
                            
                            setTimeout(() => {
                                courseCard.remove();
                                // Update stats if needed
                                updateCourseStats();
                            }, 300);
                        } else {
                            location.reload(); // Fallback refresh
                        }
                    } else {
                        alert('❌ Error deleting course: ' + data.message);
                        // Restore button state
                        deleteBtn.innerHTML = originalText;
                        deleteBtn.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ An error occurred while deleting the course. Please try again.');
                    // Restore button state
                    deleteBtn.innerHTML = originalText;
                    deleteBtn.disabled = false;
                });
            } else if (confirmation !== null) {
                alert('❌ Deletion cancelled. You must type "DELETE" exactly to confirm.');
            }
        }

        // Helper function to update course statistics after deletion
        function updateCourseStats() {
            // You can implement this to update the stats cards without full page reload
            // For now, we'll do a simple reload after a delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        function viewCourseDetails(courseId) {
            // This could open a modal or navigate to a detailed view
            // For now, let's show an alert with the course ID
            window.open(`course-info.php?id=${courseId}`, '_blank');
        }

        function filterCourses(status) {
            const courseCards = document.querySelectorAll('.course-card');
            
            courseCards.forEach(card => {
                const cardStatus = card.getAttribute('data-status');
                
                if (status === 'all' || status === '' || cardStatus === status) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
    </script>
</body>
</html>