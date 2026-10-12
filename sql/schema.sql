-- ============================================================
-- Placement Management System - Complete MariaDB / MySQL Schema
-- Includes: Tables, Constraints, Indexes, Foreign Keys, Sample Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS `placement_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `placement_db`;

-- --------------------------------------------------------
-- Table: departments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `name` VARCHAR(100) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: skills
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `category` VARCHAR(50) DEFAULT 'Technical',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: admin
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `profile_pic` VARCHAR(255) DEFAULT 'default-avatar.png',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: students
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `students` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `roll_number` VARCHAR(50) NOT NULL UNIQUE,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `department_id` INT DEFAULT NULL,
  `cgpa` DECIMAL(3,2) DEFAULT 0.00,
  `passing_year` INT DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `profile_pic` VARCHAR(255) DEFAULT 'default-avatar.png',
  `resume_pdf` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Active', 'Placed', 'Inactive') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_student_email` (`email`),
  INDEX `idx_student_dept` (`department_id`),
  INDEX `idx_student_status` (`status`),
  CONSTRAINT `fk_students_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: student_skills
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `student_skills` (
  `student_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  PRIMARY KEY (`student_id`, `skill_id`),
  CONSTRAINT `fk_ss_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ss_skill` FOREIGN KEY (`skill_id`) REFERENCES `skills` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: companies
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `companies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `website` VARCHAR(150) DEFAULT NULL,
  `industry` VARCHAR(100) DEFAULT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `description` TEXT DEFAULT NULL,
  `logo` VARCHAR(255) DEFAULT 'default-company.png',
  `status` ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Approved',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_company_email` (`email`),
  INDEX `idx_company_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: jobs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `company_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `department_id` INT DEFAULT NULL,
  `job_type` ENUM('Full Time', 'Internship', 'Part Time', 'Contract') DEFAULT 'Full Time',
  `location` VARCHAR(100) NOT NULL,
  `salary_package` VARCHAR(100) NOT NULL,
  `min_cgpa` DECIMAL(3,2) DEFAULT 0.00,
  `vacancies` INT DEFAULT 1,
  `deadline` DATE NOT NULL,
  `description` TEXT NOT NULL,
  `requirements` TEXT NOT NULL,
  `status` ENUM('Active', 'Closed') DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_job_company` (`company_id`),
  INDEX `idx_job_dept` (`department_id`),
  INDEX `idx_job_status` (`status`),
  CONSTRAINT `fk_jobs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_jobs_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: applications
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `job_id` INT NOT NULL,
  `student_id` INT NOT NULL,
  `status` ENUM('Applied', 'Shortlisted', 'Interview Scheduled', 'Selected', 'Rejected') DEFAULT 'Applied',
  `cover_note` TEXT DEFAULT NULL,
  `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_student_job` (`job_id`, `student_id`),
  INDEX `idx_app_job` (`job_id`),
  INDEX `idx_app_student` (`student_id`),
  INDEX `idx_app_status` (`status`),
  CONSTRAINT `fk_app_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_app_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: interviews
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `interviews` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `application_id` INT NOT NULL,
  `interview_date` DATETIME NOT NULL,
  `location_link` VARCHAR(255) NOT NULL,
  `round_name` VARCHAR(100) DEFAULT 'Technical Round',
  `instructions` TEXT DEFAULT NULL,
  `status` ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_interview_app` (`application_id`),
  CONSTRAINT `fk_interview_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: notifications
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_type` ENUM('admin', 'student', 'company') NOT NULL,
  `user_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_notif_user` (`user_type`, `user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: activity_logs
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_type` VARCHAR(20) NOT NULL,
  `user_id` INT DEFAULT NULL,
  `user_email` VARCHAR(100) DEFAULT NULL,
  `action` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table: placement_statistics
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `placement_statistics` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `academic_year` VARCHAR(20) NOT NULL,
  `total_students` INT DEFAULT 0,
  `placed_students` INT DEFAULT 0,
  `total_companies` INT DEFAULT 0,
  `total_offers` INT DEFAULT 0,
  `highest_package` VARCHAR(50) DEFAULT '0 LPA',
  `average_package` VARCHAR(50) DEFAULT '0 LPA',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================================
-- SAMPLE SEED DATA
-- Default Admin Password: admin123 (bcrypt hash below)
-- Default Student Password: student123
-- Default Company Password: company123
-- ============================================================

INSERT INTO `departments` (`id`, `code`, `name`) VALUES
(1, 'CSE', 'Computer Science & Engineering'),
(2, 'IT', 'Information Technology'),
(3, 'ECE', 'Electronics & Communication Engineering'),
(4, 'ME', 'Mechanical Engineering'),
(5, 'CE', 'Civil Engineering');

INSERT INTO `skills` (`id`, `name`, `category`) VALUES
(1, 'PHP', 'Backend'),
(2, 'MySQL / MariaDB', 'Database'),
(3, 'JavaScript', 'Frontend'),
(4, 'Python', 'Programming'),
(5, 'React.js', 'Frontend'),
(6, 'Node.js', 'Backend'),
(7, 'AWS & Linux', 'DevOps'),
(8, 'Java', 'Programming'),
(9, 'Data Structures & Algorithms', 'Core');

-- Default Admin Account (Password: admin123)
INSERT INTO `admin` (`id`, `full_name`, `email`, `password`, `phone`) VALUES
(1, 'System Administrator', 'admin@placement.com', '$2y$10$wc/pK/PZOP6sr3FXxRC89.a88f./zZ80oO/7oYvJ26XvCmlpXTjL.', '+1 555-0192');

-- Sample Companies (Password: company123)
INSERT INTO `companies` (`id`, `company_name`, `email`, `password`, `phone`, `website`, `industry`, `location`, `description`, `status`) VALUES
(1, 'Apex Cloud Technologies', 'hr@apexcloud.tech', '$2y$10$2ZU5m8eBJojoqV0vdX7K2uVtIjVJuHe4j3hGCBxqSMDHWxThY.Sz.', '+1 800-555-0101', 'https://apexcloud.tech', 'Cloud Computing', 'Seattle, WA', 'Leading provider of cloud infrastructure and scalable backend services.', 'Approved'),
(2, 'Nexus Software Solutions', 'careers@nexussoft.io', '$2y$10$2ZU5m8eBJojoqV0vdX7K2uVtIjVJuHe4j3hGCBxqSMDHWxThY.Sz.', '+1 800-555-0102', 'https://nexussoft.io', 'Enterprise Software', 'Austin, TX', 'Building modern enterprise Web and Mobile applications for Global 500 companies.', 'Approved'),
(3, 'Quantum Cyber Analytics', 'recruit@quantumcyber.com', '$2y$10$2ZU5m8eBJojoqV0vdX7K2uVtIjVJuHe4j3hGCBxqSMDHWxThY.Sz.', '+1 800-555-0103', 'https://quantumcyber.com', 'Cyber Security & AI', 'Boston, MA', 'Pioneering artificial intelligence and automated security threat mitigation platforms.', 'Approved');

-- Sample Students (Password: student123)
INSERT INTO `students` (`id`, `roll_number`, `full_name`, `email`, `password`, `phone`, `department_id`, `cgpa`, `passing_year`, `bio`, `status`) VALUES
(1, 'CS2024-001', 'John Doe', 'john.doe@student.edu', '$2y$10$X7I69VRCLFBjPEPUVhMKEOBoCKlgdAm2CcT7m//Guy0Gpyqoc2j8O', '+1 555-0111', 1, 3.85, 2024, 'Passionate Full Stack Developer with strong expertise in PHP, JS, and Databases.', 'Placed'),
(2, 'IT2024-042', 'Alice Smith', 'alice.smith@student.edu', '$2y$10$X7I69VRCLFBjPEPUVhMKEOBoCKlgdAm2CcT7m//Guy0Gpyqoc2j8O', '+1 555-0122', 2, 3.92, 2024, 'Backend developer interested in Distributed Systems, DevOps, and Cloud Architectures.', 'Active'),
(3, 'ECE2024-108', 'Robert Johnson', 'robert.j@student.edu', '$2y$10$X7I69VRCLFBjPEPUVhMKEOBoCKlgdAm2CcT7m//Guy0Gpyqoc2j8O', '+1 555-0133', 3, 3.65, 2024, 'Embedded software engineer with experience in IoT protocols and Python.', 'Active');

INSERT INTO `student_skills` (`student_id`, `skill_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 7),
(2, 4), (2, 6), (2, 7), (2, 8),
(3, 4), (3, 8), (3, 9);

-- Sample Jobs
INSERT INTO `jobs` (`id`, `company_id`, `title`, `department_id`, `job_type`, `location`, `salary_package`, `min_cgpa`, `vacancies`, `deadline`, `description`, `requirements`, `status`) VALUES
(1, 1, 'Senior Cloud Engineer Trainee', 1, 'Full Time', 'Seattle, WA (Hybrid)', '$95,000 / annum', 3.50, 5, DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY), 'Join our cloud infrastructure team to build high throughput APIs and serverless architectures.', 'Strong PHP/Python backend skills, understanding of Nginx/Linux environments, MySQL database tuning.', 'Active'),
(2, 2, 'Full Stack Web Developer', 1, 'Full Time', 'Austin, TX', '$88,000 / annum', 3.20, 10, DATE_ADD(CURRENT_DATE, INTERVAL 20 DAY), 'Develop responsive Web Portals using Modern HTML5/CSS3, JavaScript, and PHP MySQLi.', 'Proficiency in HTML5, CSS3, JavaScript ES6, MySQL, REST APIs, Git.', 'Active'),
(3, 3, 'Security Analyst Intern', 3, 'Internship', 'Remote', '$35 / hour', 3.00, 3, DATE_ADD(CURRENT_DATE, INTERVAL 15 DAY), 'Monitor network anomalies, write Python automated scanners, and audit database access logs.', 'Basic networking knowledge, Linux shell scripting, SQL security principles.', 'Active');

-- Sample Applications
INSERT INTO `applications` (`id`, `job_id`, `student_id`, `status`, `cover_note`) VALUES
(1, 1, 1, 'Selected', 'I am very excited about Apex Cloud Technologies and have built multiple cloud backend projects in PHP/AWS.'),
(2, 2, 2, 'Interview Scheduled', 'I have worked extensively with modern Web stack and microservices during my internship.'),
(3, 3, 3, 'Shortlisted', 'Strong interest in hardware security and Linux kernel debugging.');

-- Sample Interview
INSERT INTO `interviews` (`id`, `application_id`, `interview_date`, `location_link`, `round_name`, `instructions`, `status`) VALUES
(1, 2, DATE_ADD(NOW(), INTERVAL 3 DAY), 'https://meet.google.com/xyz-abc-pqr', 'Technical Round 1 - Live Coding', 'Please ensure camera is turned on and bring your updated resume PDF.', 'Scheduled');

-- Sample Statistics
INSERT INTO `placement_statistics` (`id`, `academic_year`, `total_students`, `placed_students`, `total_companies`, `total_offers`, `highest_package`, `average_package`) VALUES
(1, '2023-2024', 450, 385, 42, 410, '$125,000 / yr', '$82,500 / yr');

-- Sample Audit Log
INSERT INTO `activity_logs` (`user_type`, `user_id`, `user_email`, `action`, `ip_address`) VALUES
('system', 1, 'admin@placement.com', 'Database initialized with standard placement seed schema.', '127.0.0.1');
