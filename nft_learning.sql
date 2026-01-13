-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 06:06 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nft_learning`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `full_name`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$ZvWtzpFMIMHgNFXz2xZObedaunpfZwbjrpoy7sptN1GjxiVa1zBG6', '2025-08-28 08:22:39');

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `target_type` varchar(50) NOT NULL,
  `target_id` int(11) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `creator_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` int(11) NOT NULL,
  `students_enrolled` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `status` enum('draft','published','archived') DEFAULT 'published',

  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL,

  `video_path` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `nft_certificate_image` varchar(255) DEFAULT NULL,
  `duration_hours` int(11) DEFAULT 0,
  `approved_at` timestamp NULL DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,

  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `creator_id`, `course_name`, `description`, `category`, `price`, `duration`, `students_enrolled`, `rating`, `total_reviews`, `status`, `created_at`, `updated_at`, `video_path`, `thumbnail`, `nft_certificate_image`, `duration_hours`, `approved_at`, `rejected_at`, `suspended_at`, `rejection_reason`) VALUES
(3, 4, 'dfa', 'dasfasfdasgadgwf', 'Web Development', 2.00, 12, 1, 0.00, 0, 'published', '2025-09-06 10:51:38', '2025-09-07 09:42:44', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(4, 5, 'Web Development For Pros', 'dfghqrhyeqtr reqgrqfg weerwq', 'Web Development', 12.00, 32, 2, 0.00, 0, 'published', '2025-09-07 03:03:29', '2025-09-07 09:49:09', 'uploads/course_videos/course_4_1757154902_68bc0e564fb46.mp4', NULL, NULL, 0, NULL, NULL, NULL, NULL),
(5, 5, 'Management', 'lorem impsom fasd wef', 'Mobile Development', 12.00, 22, 2, 0.00, 0, 'published', '2025-09-07 04:10:11', '2025-09-07 07:25:36', NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL),
(19, 6, 'The Complete Python Developer', 'Learn Python from scratch, get hired, and have fun along the way with the most modern, up-to-date Python course on Udemy (we use the latest version of Python). This course is focused on efficiency: never spend time on confusing, out of date, incomplete Python tutorials anymore.\\r\\n\\r\\nThis comprehensive and project based course will introduce you to all of the modern skills of a Python developer (Python 3) and along the way, we will build over 12 real world projects to add to your portfolio (You will get access to all the the code from the 12+ projects we build, so that you can put them on your portfolio right away)!', 'Data Science', 99.00, 10, 0, 0.00, 0, 'published', '2025-09-09 06:52:58', '2025-09-09 06:52:58', 'uploads/course_videos/course_6_1757400778_68bfcecab25d6.mp4', 'uploads/course_thumbnails/course_6_1757400778_68bfcecab21f3.png', 'uploads/nft_certificates/cert_6_1757400778_68bfcecab2a0a.png', 0, NULL, NULL, NULL, NULL),
(20, 6, 'The Complete JavaScript Course 2025: From Zero to Expert!', 'Just updated with ES2024 and ES2025!\\r\\n\\r\\n\\\"Really, really well made course. Super in-depth, with great challenges and projects that will solidify your Javascript understanding. I found the lectures were paced perfectly -- Jonas doesn\\\'t skip over anything that might be useful to a JS developer\\\" — Carson Bartholomew\\r\\n\\r\\n\\r\\n\\r\\nJavaScript is the most popular programming language in the world. It powers the entire modern web. It provides millions of high-paying jobs all over the world.\\r\\n\\r\\nThat\\\'s why you want to learn JavaScript too. And you came to the right place!', 'Web Development', 20.00, 5, 1, 0.00, 0, 'published', '2025-09-09 06:57:21', '2025-09-10 06:04:37', 'uploads/course_videos/course_6_1757401041_68bfcfd1ce7f8.mp4', 'uploads/course_thumbnails/course_6_1757401041_68bfcfd1ce391.png', 'uploads/nft_certificates/cert_6_1757401041_68bfcfd1ceb27.png', 0, NULL, NULL, NULL, NULL),
(21, 6, 'Master Ethereum & Solidity Programming From Scratch', 'This course covers every core concept of Ethereum, Solidity and Blockchain Technology with 5 Hands-On Projects.\\r\\n\\r\\nThis Ethereum and Solidity Programming course covers every major topic of Ethereum and Solidity, including Smart Contracts Compilation and Deployment on the Blockchain, ABI, Bytecode, Transactions and Calls, Gas, State Variables, Solidity Global Variables, Getter and Setter Functions, Receive, Fallback and Payable Functions, all Solidity Data Types, Events, Accessing and Protecting the Contract’s Balance, Visibility Specifiers and many more!\\r\\n\\r\\nThis Course is NOT FOR COMPLETE BEGINNERS in Programming.\\r\\n\\r\\nI’m constantly updating the course to be the most comprehensive, yet straightforward, Ethereum, Solidity & Blockchain Programming course on the market!', 'Blockchain', 50.00, 3, 1, 0.00, 0, 'published', '2025-09-09 07:00:56', '2025-09-10 05:51:22', 'uploads/course_videos/course_6_1757401256_68bfd0a85ff23.mp4', 'uploads/course_thumbnails/course_6_1757401256_68bfd0a85fa93.jfif', 'uploads/nft_certificates/cert_6_1757401256_68bfd0a860283.jfif', 0, NULL, NULL, NULL, NULL),
(22, 6, 'NodeJS - The Complete Guide (MVC, REST APIs, GraphQL, Deno)', 'Join the most comprehensive Node.js course on Udemy and learn Node in both a practical and a theory-based way!\\r\\n\\r\\n-\\r\\n\\r\\nNode.js is probably THE most popular and modern server-side programming language you can learn these days!\\r\\n\\r\\nNode.js developers are in high demand and the language is used for everything from traditional web apps with server-side rendered views over REST APIs all the way up to GraphQL APIs and real-time web services. Not to mention its applications in build workflows for projects of all sizes.\\r\\n\\r\\nThis course will teach you all of that! From scratch with zero prior knowledge assumed. Though if you do bring some knowledge, you\\\'ll of course be able to quickly jump into the course modules that are most interesting to you.', 'Web Development', 20.00, 4, 2, 0.00, 0, 'published', '2025-09-09 07:21:15', '2025-09-10 06:25:20', 'uploads/course_videos/course_6_1757402475_68bfd56b3836c.mp4', 'uploads/course_thumbnails/course_6_1757402475_68bfd56b37ea0.png', 'uploads/nft_certificates/cert_6_1757402475_68bfd56b3870e.png', 0, NULL, NULL, NULL, NULL),
(23, 6, 'Management', 'fqeg ewgwe', 'Data Science', 12.00, 32, 0, 0.00, 0, 'published', '2025-09-10 06:44:38', '2025-09-10 06:44:38', 'uploads/course_videos/course_6_1757486678_68c11e5648241.mp4', 'uploads/course_thumbnails/course_6_1757486678_68c11e5647d7e.jpg', NULL, 0, NULL, NULL, NULL, NULL),
(24, 6, 'Management', 'fqeg ewgwe', 'Data Science', 12.00, 32, 1, 0.00, 0, 'published', '2025-09-10 06:45:02', '2025-09-10 07:03:16', 'uploads/course_videos/course_6_1757486702_68c11e6ed518b.mp4', 'uploads/course_thumbnails/course_6_1757486702_68c11e6ed4c1f.jpg', NULL, 0, NULL, NULL, NULL, NULL),
(28, 7, 'Python for beginners', '312f ewf 2', 'Artificial Intelligence', 342.00, 321, 1, 0.00, 0, 'published', '2025-09-10 12:20:36', '2025-09-10 12:57:57', 'uploads/course_videos/course_7_1757506836_68c16d14c4ecb.mp4', 'uploads/course_thumbnails/course_7_1757506836_68c16d14c4b16.jfif', NULL, 0, NULL, NULL, NULL, NULL),
(30, 7, 'Managementqwd', 'fawedf eq2wg ewqfg', 'Data Science', 21.00, 32, 1, 0.00, 0, 'published', '2025-09-10 12:40:00', '2025-09-10 12:45:07', 'uploads/course_videos/course_7_1757508000_68c171a04fb6a.mp4', 'uploads/course_thumbnails/course_7_1757508000_68c171a04f769.jpg', NULL, 0, NULL, NULL, NULL, NULL),
(31, 7, 'Web Development For Pros', 'rewq rfwqef', 'Web Development', 2.00, 12, 0, 0.00, 0, 'published', '2025-09-10 13:00:09', '2025-09-10 13:00:09', 'uploads/course_videos/course_7_1757509209_68c176590debf.mp4', 'uploads/course_thumbnails/course_7_1757509209_68c176590dad6.jpg', NULL, 0, NULL, NULL, NULL, NULL),
(32, 7, 'AI for beginners', '312d wef ewq feqwg', 'Blockchain', 221.00, 32, 1, 0.00, 0, 'published', '2025-09-10 13:11:16', '2025-09-10 13:12:48', 'uploads/course_videos/course_7_1757509876_68c178f444d25.mp4', 'uploads/course_thumbnails/course_7_1757509876_68c178f4444ea.jfif', 'uploads/nft_certificates/cert_7_1757509876_68c178f4450c5.png', 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `course_categories`
--

CREATE TABLE `course_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_categories`
--

INSERT INTO `course_categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Web Development', 'Frontend and backend web development courses', '2025-09-07 03:19:55'),
(2, 'Mobile Development', 'iOS and Android app development', '2025-09-07 03:19:55'),
(3, 'Data Science', 'Data analysis, statistics, and data visualization', '2025-09-07 03:19:55'),
(4, 'Machine Learning', 'AI and machine learning algorithms', '2025-09-07 03:19:55'),
(5, 'Blockchain', 'Cryptocurrency and blockchain technology', '2025-09-07 03:19:55'),
(6, 'UI/UX Design', 'User interface and user experience design', '2025-09-07 03:19:55'),
(7, 'Digital Marketing', 'Online marketing and social media strategies', '2025-09-07 03:19:55'),
(8, 'Business', 'Business strategy and entrepreneurship', '2025-09-07 03:19:55'),
(9, 'Programming', 'General programming languages and concepts', '2025-09-07 03:19:55'),
(10, 'Cybersecurity', 'Information security and ethical hacking', '2025-09-07 03:19:55');

-- --------------------------------------------------------

--
-- Table structure for table `creators`
--

CREATE TABLE `creators` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_courses` int(11) DEFAULT 0,
  `total_students` int(11) DEFAULT 0,
  `total_revenue` decimal(10,2) DEFAULT 0.00,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `profile_picture` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `expertise` varchar(255) DEFAULT NULL,
  `wallet_address` varchar(255) DEFAULT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `social_linkedin` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `social_website` varchar(255) DEFAULT NULL,
  `average_rating` decimal(3,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `creators`
--

INSERT INTO `creators` (`id`, `full_name`, `email`, `id_number`, `password`, `created_at`, `total_courses`, `total_students`, `total_revenue`, `rating`, `total_reviews`, `profile_picture`, `bio`, `expertise`, `wallet_address`, `is_verified`, `social_linkedin`, `social_twitter`, `social_website`, `average_rating`) VALUES
(1, 'ishan', 'ishan@gmail.com', '789456', '$2y$10$CHwNtld4wuhaBgI1wlin5uLC0KJr27b4WeL/N8NypxuPQP.er4ITO', '2025-08-28 08:11:22', 0, 0, 0.00, 0.00, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00),
(2, 'adisa', 'adisa@gmail.com', '123456789', '$2y$10$Cpl9iBerH0idIzYdYXEv5OSp4OhXJ95dkXq2EQ.etik2mJ4s73IPe', '2025-08-28 13:46:57', 0, 0, 0.00, 0.00, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00),
(3, 'Ishan Wijekoon', 'creator@gmail.com', '2223323', '$2y$10$zbcC5z2R.UjEGcdEjIKAa.1DIylojmGXdg9igRD0UnbN6/vVEZJ8S', '2025-09-05 06:17:33', 0, 0, 0.00, 0.00, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00),
(4, 'kavindu', 'k@gmail.com', '123456789', '$2y$10$zbnEK0CCsFxzpAXsAP6gs.ymxpIS1nVuwwTnKTWViUYg8QercDfzS', '2025-09-06 09:38:09', 1, 1, 0.00, 0.00, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00),
(5, 'Ishan Wijekoon', 'ishancoc295@gmail.com', '20021002', '$2y$10$.rqUvPVD4WvPawRddl4D0upjG4JZdyJ.jaNcYUsDjQjjaOs8ATKVq', '2025-09-07 02:39:12', 2, 3, 0.00, 0.00, 0, 'uploads/creator_pictures/creator_5_1757214178.png', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00),
(6, 'Adheesa Darmakeethi', 'kavin@gmail.com', '5557321657', '$2y$10$.m1tIyp3n4Bq.25eC4hFtedH74UxW3ryqE.wnSlQFCSc0USPHy7Si', '2025-09-07 07:26:37', 6, 5, 0.00, 0.00, 0, 'uploads/creator_pictures/creator_6_1757486145.png', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00),
(7, 'dileka', 'isha@gmail.com', '4353463462', '$2y$10$M61/Ia/QGXbBD1.gSCl7GuhZ4JDmco6acIvv77F2jg4E91zCtLfwq', '2025-09-10 06:16:31', 4, 3, 0.00, 0.00, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `learner_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `progress` decimal(5,2) DEFAULT 0.00,
  `completed` tinyint(1) DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completion_date` timestamp NULL DEFAULT NULL,
  `certificate_issued` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `learner_id`, `course_id`, `enrolled_at`, `progress`, `completed`, `completed_at`, `completion_date`, `certificate_issued`) VALUES
(1, 6, 5, '2025-09-07 07:22:36', 0.00, 0, NULL, NULL, 0),
(2, 7, 5, '2025-09-07 07:25:36', 0.00, 0, NULL, NULL, 0),
(3, 7, 6, '2025-09-07 07:35:49', 0.00, 0, NULL, NULL, 0),
(4, 7, 7, '2025-09-07 08:10:50', 0.00, 0, NULL, NULL, 0),
(5, 7, 4, '2025-09-07 08:13:30', 0.00, 0, NULL, NULL, 0),
(6, 6, 4, '2025-09-07 08:32:55', 0.00, 0, NULL, NULL, 0),
(7, 7, 8, '2025-09-07 08:37:32', 100.00, 1, '2025-09-07 08:41:08', NULL, 0),
(8, 7, 3, '2025-09-07 09:42:44', 0.00, 0, NULL, NULL, 0),
(9, 5, 4, '2025-09-07 09:49:09', 0.00, 0, NULL, NULL, 0),
(10, 6, 8, '2025-09-07 12:38:25', 100.00, 1, '2025-09-07 12:38:57', NULL, 0),
(11, 7, 17, '2025-09-08 20:25:16', 100.00, 1, '2025-09-08 20:25:34', NULL, 0),
(12, 7, 18, '2025-09-08 20:45:14', 100.00, 1, '2025-09-08 20:45:34', '2025-09-08 21:01:00', 1),
(13, 7, 22, '2025-09-09 14:23:25', 100.00, 1, '2025-09-09 14:23:54', '2025-09-09 14:23:54', 1),
(14, 7, 21, '2025-09-10 05:51:22', 100.00, 1, '2025-09-10 05:52:01', '2025-09-10 05:52:01', 1),
(15, 7, 20, '2025-09-10 06:04:37', 100.00, 1, '2025-09-10 06:05:02', '2025-09-10 06:05:02', 1),
(16, 6, 22, '2025-09-10 06:25:20', 0.00, 0, NULL, NULL, 0),
(17, 6, 24, '2025-09-10 07:03:16', 100.00, 1, '2025-09-10 07:03:49', '2025-09-10 07:03:49', 0),
(18, 7, 30, '2025-09-10 12:45:07', 100.00, 1, '2025-09-10 12:45:29', '2025-09-10 12:45:29', 0),
(19, 7, 28, '2025-09-10 12:57:57', 100.00, 1, '2025-09-10 12:58:05', '2025-09-10 12:58:05', 0),
(20, 6, 32, '2025-09-10 13:12:48', 100.00, 1, '2025-09-10 13:12:57', '2025-09-10 13:12:57', 1);

-- --------------------------------------------------------

--
-- Table structure for table `learners`
--

CREATE TABLE `learners` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `id_number` varchar(100) NOT NULL,
  `wallet_address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL,
  `total_courses_enrolled` int(11) DEFAULT 0,
  `total_courses_completed` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learners`
--

INSERT INTO `learners` (`id`, `full_name`, `email`, `id_number`, `wallet_address`, `password`, `created_at`, `profile_picture`, `total_courses_enrolled`, `total_courses_completed`) VALUES
(1, 'Kavindu Hasaranga Jayawardhana', 'kavinduhasaranga2017@gmail.com', '12345678', 'Jayawardhana Tex Neluwa Road Alapaladeniya', '$2y$10$SdDv36tEKiORJfmohEB3C.czmFsgH12gL3hnAv4sR0zwZKEwHJJyi', '2025-08-28 08:00:14', NULL, 0, 0),
(3, 'Ishan WIjekoon', 'kavindu@gmail.com', '20021002', 'fewfewfgqw', '$2y$10$FeDKrX41DfUo0a51gbZSdeIUzzgUexMFG3/2JPVHKIH.0niL0LeNu', '2025-08-28 13:44:57', NULL, 0, 0),
(4, 'Ishan Wijekoon', 'ishan@gmail.com', '200213302971', '4475747fsd69r4', '$2y$10$iv2H3A.Bt1ZrScuJf0E9tOAtu8x12RKfX1Qa8TIrI0h.zQlbs5dMO', '2025-09-05 05:42:21', NULL, 0, 0),
(5, 'Isuru', 'isu@gmail.com', '6665548', 'f4asd965g4few98g4', '$2y$10$HbQvpPZnlQEUeZx3ZMRd3.THAdYpAS4GM7OYGgkB6nV6ePKqydbtW', '2025-09-05 06:07:49', 'uploads/profile_pictures/profile_5_1757052956.jpg', 0, 0),
(6, 'Ishan Wijekoon', 'ishancoc295@gmail.com', '20021302571', 'fdasfwefwaf', '$2y$10$dUba64Nbgdua9BtI46L6PeQz3wxFgoIBw10qJaZ0T5HvzOSjVHfD2', '2025-09-07 02:30:04', 'uploads/profile_pictures/profile_6_1757228388.png', 0, 0),
(7, 'Kavindu Jayawardana', 'kavin@gmail.com', '6668877454', '22674dasv896q74', '$2y$10$FzK1chAnNXiVJM7U6VTRfeRxZI3xhjJoXmIg8qR2eQKiGwZH9C8C.', '2025-09-07 07:24:57', 'uploads/profile_pictures/profile_7_1757365667.jpg', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `nft_certificates`
--

CREATE TABLE `nft_certificates` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `learner_id` int(11) NOT NULL,
  `creator_id` int(11) NOT NULL,
  `nft_key` varchar(64) NOT NULL,
  `certificate_hash` varchar(128) NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `learner_name` varchar(255) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `creator_name` varchar(255) NOT NULL,
  `certificate_image_path` varchar(255) NOT NULL,
  `blockchain_tx_hash` varchar(255) DEFAULT NULL,
  `verification_url` varchar(500) DEFAULT NULL,
  `status` enum('pending','issued','verified','revoked') DEFAULT 'pending',
  `metadata` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nft_certificates`
--

INSERT INTO `nft_certificates` (`id`, `course_id`, `learner_id`, `creator_id`, `nft_key`, `certificate_hash`, `issued_at`, `learner_name`, `course_name`, `creator_name`, `certificate_image_path`, `blockchain_tx_hash`, `verification_url`, `status`, `metadata`) VALUES
(1, 18, 7, 6, 'NFT936A6064183ACDA7A64C47E7060FAA0E1757365260', '1036e1b940cb8064d4ede5f116ab996116236cfcb40b6e6ce26eaaab5700764d', '2025-09-08 21:01:00', 'Kavindu Jayawardana', 'Game Development', 'Adheesa Darmakeethi', 'uploads/nft_certificates/cert_6_1757364264_68bf4028d6e5e.jpg', NULL, NULL, 'issued', NULL),
(2, 22, 7, 6, 'NFT85D5C267D91328DFA1C88211FAE5896C1757427834', 'e57f41deb16eb83f82a3809c1dbc23bdc3bcdcb9bfb79cea35e895922b360910', '2025-09-09 14:23:54', 'Kavindu Jayawardana', 'NodeJS - The Complete Guide (MVC, REST APIs, GraphQL, Deno)', 'Adheesa Darmakeethi', 'uploads/nft_certificates/cert_6_1757402475_68bfd56b3870e.png', NULL, NULL, 'issued', NULL),
(3, 21, 7, 6, 'NFTE256694842E1BF1AC329811298A39DCC1757483521', '251db574cafd964491e02e673ef0631a3cd36f7686961ddd5065a3dc319d8598', '2025-09-10 05:52:01', 'Kavindu Jayawardana', 'Master Ethereum & Solidity Programming From Scratch', 'Adheesa Darmakeethi', 'uploads/nft_certificates/cert_6_1757401256_68bfd0a860283.jfif', NULL, NULL, 'issued', NULL),
(4, 20, 7, 6, 'NFT73CB1B44394C198E341F18605718C6E61757484302', '97ba117b4faff98a376bbef7cdf902be95a512518164bdceec587e309b39330b', '2025-09-10 06:05:02', 'Kavindu Jayawardana', 'The Complete JavaScript Course 2025: From Zero to Expert!', 'Adheesa Darmakeethi', 'uploads/nft_certificates/cert_6_1757401041_68bfcfd1ceb27.png', NULL, NULL, 'issued', NULL),
(5, 32, 6, 7, 'NFT397F1772112153F15AED1B445E174AAE1757509977', '3c2c29d82173fe45cf087282e1b6149290a7c3a0d8b71d310ab7a71e5f38c73f', '2025-09-10 13:12:57', 'Ishan Wijekoon', 'AI for beginners', 'dileka', 'uploads/nft_certificates/cert_7_1757509876_68c178f4450c5.png', NULL, NULL, 'issued', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `nft_settings`
--

CREATE TABLE `nft_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nft_settings`
--

INSERT INTO `nft_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'nft_enabled', '1', 'Enable/disable NFT certificate system', '2025-09-08 20:39:37'),
(2, 'certificate_template_width', '800', 'Certificate image width in pixels', '2025-09-08 20:39:37'),
(3, 'certificate_template_height', '600', 'Certificate image height in pixels', '2025-09-08 20:39:37'),
(4, 'certificate_format', 'png', 'Certificate image format (png, jpg)', '2025-09-08 20:39:37'),
(5, 'blockchain_network', 'ethereum', 'Blockchain network for NFT minting', '2025-09-08 20:39:37'),
(6, 'auto_mint_enabled', '0', 'Automatically mint NFT certificates', '2025-09-08 20:39:37'),
(7, 'verification_base_url', 'https://yoursite.com/verify/', 'Base URL for certificate verification', '2025-09-08 20:39:37');

-- --------------------------------------------------------

--
-- Table structure for table `nft_verifications`
--

CREATE TABLE `nft_verifications` (
  `id` int(11) NOT NULL,
  `certificate_id` int(11) NOT NULL,
  `verification_code` varchar(8) NOT NULL,
  `verification_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `verified_at` timestamp NULL DEFAULT NULL,
  `verifier_ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nft_verifications`
--

INSERT INTO `nft_verifications` (`id`, `certificate_id`, `verification_code`, `verification_count`, `created_at`, `verified_at`, `verifier_ip`) VALUES
(1, 1, 'E92CA031', 10, '2025-09-08 21:01:00', '2025-09-09 05:09:31', '::1'),
(2, 2, '0D70B376', 3, '2025-09-09 14:23:54', '2025-09-10 15:57:46', '::1'),
(3, 3, '7E460300', 1, '2025-09-10 05:52:01', '2025-09-10 05:52:23', '::1'),
(4, 4, '5F9DF8E3', 2, '2025-09-10 06:05:02', '2025-09-14 11:54:45', '::1'),
(5, 5, 'E0063049', 5, '2025-09-10 13:12:57', '2025-09-10 13:59:45', '::1');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`(191));


--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_target` (`target_type`,`target_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator_id` (`creator_id`);

--
-- Indexes for table `course_categories`
--
ALTER TABLE `course_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `creators`
--
ALTER TABLE `creators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`(191));


--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`learner_id`,`course_id`),
  ADD KEY `idx_learner` (`learner_id`),
  ADD KEY `idx_course` (`course_id`),
  ADD KEY `idx_progress` (`progress`),
  ADD KEY `idx_completed` (`completed`);

--
-- Indexes for table `learners`
--
ALTER TABLE `learners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`(191));


--
-- Indexes for table `nft_certificates`
--
ALTER TABLE `nft_certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nft_key` (`nft_key`(191)),
  ADD UNIQUE KEY `certificate_hash` (`certificate_hash`(191)),
  ADD KEY `idx_course_learner` (`course_id`,`learner_id`),
  ADD KEY `idx_nft_key` (`nft_key`),
  ADD KEY `idx_certificate_hash` (`certificate_hash`);

--
-- Indexes for table `nft_settings`
--
ALTER TABLE `nft_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`(191)),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `nft_verifications`
--
ALTER TABLE `nft_verifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `verification_code` (`verification_code`(191)),
  ADD KEY `idx_certificate_id` (`certificate_id`),
  ADD KEY `idx_verification_code` (`verification_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `course_categories`
--
ALTER TABLE `course_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `creators`
--
ALTER TABLE `creators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `learners`
--
ALTER TABLE `learners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `nft_certificates`
--
ALTER TABLE `nft_certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `nft_settings`
--
ALTER TABLE `nft_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `nft_verifications`
--
ALTER TABLE `nft_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`creator_id`) REFERENCES `creators` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `nft_verifications`
--
ALTER TABLE `nft_verifications`
  ADD CONSTRAINT `fk_nft_verifications_certificate` FOREIGN KEY (`certificate_id`) REFERENCES `nft_certificates` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
