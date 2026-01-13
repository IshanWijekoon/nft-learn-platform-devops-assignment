SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8 */;

-- =========================
-- TABLE: admins
-- =========================
CREATE TABLE admins (
  id INT(11) NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(191) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO admins VALUES
(1,'admin','admin@gmail.com','$2y$10$ZvWtzpFMIMHgNFXz2xZObedaunpfZwbjrpoy7sptN1GjxiVa1zBG6','2025-08-28 08:22:39');

-- =========================
-- TABLE: admin_actions
-- =========================
CREATE TABLE admin_actions (
  id INT(11) NOT NULL AUTO_INCREMENT,
  admin_id INT(11) NOT NULL,
  action_type VARCHAR(50) NOT NULL,
  target_type VARCHAR(50) NOT NULL,
  target_id INT(11) NOT NULL,
  details TEXT,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_admin_id (admin_id),
  KEY idx_created_at (created_at),
  KEY idx_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: creators
-- =========================
CREATE TABLE creators (
  id INT(11) NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(191) NOT NULL,
  id_number VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_courses INT DEFAULT 0,
  total_students INT DEFAULT 0,
  total_revenue DECIMAL(10,2) DEFAULT 0.00,
  rating DECIMAL(3,2) DEFAULT 0.00,
  total_reviews INT DEFAULT 0,
  profile_picture VARCHAR(255),
  bio TEXT,
  expertise VARCHAR(255),
  wallet_address VARCHAR(255),
  is_verified TINYINT(1) DEFAULT 0,
  social_linkedin VARCHAR(255),
  social_twitter VARCHAR(255),
  social_website VARCHAR(255),
  average_rating DECIMAL(3,2) DEFAULT 0.00,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: courses
-- =========================
CREATE TABLE courses (
  id INT(11) NOT NULL AUTO_INCREMENT,
  creator_id INT(11) NOT NULL,
  course_name VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(100) NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  duration INT(11) NOT NULL,
  students_enrolled INT DEFAULT 0,
  rating DECIMAL(3,2) DEFAULT 0.00,
  total_reviews INT DEFAULT 0,
  status ENUM('draft','published','archived') DEFAULT 'published',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  video_path VARCHAR(500),
  thumbnail VARCHAR(255),
  nft_certificate_image VARCHAR(255),
  duration_hours INT DEFAULT 0,
  approved_at TIMESTAMP NULL,
  rejected_at TIMESTAMP NULL,
  suspended_at TIMESTAMP NULL,
  rejection_reason TEXT,
  PRIMARY KEY (id),
  KEY creator_id (creator_id),
  CONSTRAINT fk_courses_creator
    FOREIGN KEY (creator_id) REFERENCES creators(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: learners
-- =========================
CREATE TABLE learners (
  id INT(11) NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(255) NOT NULL,
  email VARCHAR(191) NOT NULL,
  id_number VARCHAR(100) NOT NULL,
  wallet_address VARCHAR(255) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  profile_picture VARCHAR(255),
  total_courses_enrolled INT DEFAULT 0,
  total_courses_completed INT DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: enrollments
-- =========================
CREATE TABLE enrollments (
  id INT(11) NOT NULL AUTO_INCREMENT,
  learner_id INT(11) NOT NULL,
  course_id INT(11) NOT NULL,
  enrolled_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  progress DECIMAL(5,2) DEFAULT 0.00,
  completed TINYINT(1) DEFAULT 0,
  completed_at TIMESTAMP NULL,
  completion_date TIMESTAMP NULL,
  certificate_issued TINYINT(1) DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY unique_enrollment (learner_id, course_id),
  KEY idx_learner (learner_id),
  KEY idx_course (course_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: nft_certificates
-- =========================
CREATE TABLE nft_certificates (
  id INT(11) NOT NULL AUTO_INCREMENT,
  course_id INT(11) NOT NULL,
  learner_id INT(11) NOT NULL,
  creator_id INT(11) NOT NULL,
  nft_key VARCHAR(64) NOT NULL,
  certificate_hash VARCHAR(128) NOT NULL,
  issued_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  learner_name VARCHAR(255) NOT NULL,
  course_name VARCHAR(255) NOT NULL,
  creator_name VARCHAR(255) NOT NULL,
  certificate_image_path VARCHAR(255) NOT NULL,
  blockchain_tx_hash VARCHAR(255),
  verification_url VARCHAR(500),
  status ENUM('pending','issued','verified','revoked') DEFAULT 'pending',
  metadata TEXT,
  PRIMARY KEY (id),
  UNIQUE KEY nft_key (nft_key),
  UNIQUE KEY certificate_hash (certificate_hash),
  KEY idx_course_learner (course_id, learner_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: nft_settings
-- =========================
CREATE TABLE nft_settings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  setting_key VARCHAR(191) NOT NULL,
  setting_value TEXT NOT NULL,
  description TEXT,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- =========================
-- TABLE: nft_verifications
-- =========================
CREATE TABLE nft_verifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  certificate_id INT(11) NOT NULL,
  verification_code VARCHAR(191) NOT NULL,
  verification_count INT DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  verified_at TIMESTAMP NULL,
  verifier_ip VARCHAR(45),
  PRIMARY KEY (id),
  UNIQUE KEY verification_code (verification_code),
  KEY idx_certificate_id (certificate_id),
  CONSTRAINT fk_nft_verifications_certificate
    FOREIGN KEY (certificate_id)
    REFERENCES nft_certificates(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

COMMIT;
