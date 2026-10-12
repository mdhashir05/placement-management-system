# Placement Management System (PMS)

A modern, production-grade Campus Placement and Corporate Recruitment Portal built with **Core PHP 8.x**, **MySQLi Prepared Statements**, **MariaDB**, **Bootstrap 5**, **Chart.js**, and vanilla CSS with glassmorphic UI aesthetics.

---

## 🌟 Key Features

### 👨‍🎓 Student Portal
* **Student Onboarding & Authentication**: Secure registration and login with session timeout protection and bcrypt hashing.
* **Profile & Resume Management**: Update academic credentials (CGPA, Passing Year, Department), career bio, upload profile avatar, and upload PDF resume with inline PDF viewer.
* **Browse Jobs & 1-Click Application**: Live keyword search across active campus openings with dynamic filtering and cover note submission.
* **Application Tracker**: Real-time status pipeline tracking (`Applied` ➔ `Shortlisted` ➔ `Interview Scheduled` ➔ `Selected` / `Rejected`).
* **Technical Interview Room**: Schedule viewing with direct video conferencing meeting room URLs (Google Meet / Zoom / MS Teams).

### 🏢 Company / Recruiter Portal
* **Corporate Onboarding**: Register company details, corporate website, headquarters, industry domain, and logo.
* **Job Vacancy Posting**: Create and publish job openings with package details, eligibility criteria, CGPA cutoff, vacancies, and application deadlines.
* **Applicant Review Dashboard**: Review applicant portfolios, inspect uploaded PDF resumes, and filter candidates.
* **Status Workflow & Interview Scheduling**: Update candidate statuses (Shortlist, Reject, Select) and schedule video evaluation rounds with custom instructions.
* **Recruitment Analytics**: Visual application conversion funnel and selection percentage breakdown.

### 🛡️ Admin Portal
* **Executive Metrics Dashboard**: Real-time metrics counters for students, companies, job openings, applications, interviews, and successful placements.
* **Interactive Chart.js Visualizations**: Department-wise placement distribution doughnut chart and monthly offer generation bar chart.
* **Master Records Management**: Manage Students, Companies (Approve / Reject), Jobs, Applications, Departments, and Skills.
* **Reports & Data Export**: Instant CSV download for placed students lists and comprehensive application records.
* **Security & Audit Logs**: Detailed audit logging tracking user roles, emails, actions, IP addresses, and timestamps.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.3+ (Core PHP, procedural/modular, zero framework overhead) |
| **Database** | MariaDB 10.x / MySQL 8.x (InnoDB, Foreign Keys, Indexes) |
| **Database Driver** | MySQLi Prepared Statements (100% SQL-injection safe) |
| **Frontend** | HTML5, CSS3 (Glassmorphism + Blue-White Theme), JavaScript ES6 |
| **CSS Framework** | Bootstrap 5.3.2 |
| **Icons & Fonts** | Font Awesome 6.4, Google Fonts (Inter) |
| **Charts & Visuals** | Chart.js |
| **Web Server** | Nginx / Apache / PHP Built-in Server |

---

## 📁 Folder Structure

```text
placement-management-system/
├── admin/                     # Administrator control center
│   ├── index.php              # Analytics dashboard & graphs
│   ├── students.php           # Student moderation & status
│   ├── companies.php          # Company approval & review
│   ├── jobs.php               # Campus drive management
│   ├── applications.php       # Global application tracker
│   ├── interviews.php         # Interview schedule oversight
│   ├── departments.php        # Department & Skill tags master
│   ├── reports.php            # CSV export & placement statistics
│   └── activity_logs.php      # Audit log viewer
├── student/                   # Student candidate portal
│   ├── index.php              # Student dashboard
│   ├── profile.php            # Edit profile, avatar & PDF resume
│   ├── jobs.php               # Browse openings & submit applications
│   ├── companies.php          # View hiring partners
│   ├── applications.php       # Status timeline
│   ├── interviews.php         # Scheduled video rounds
│   └── notifications.php      # Alert messages
├── company/                   # Corporate recruiter portal
│   ├── index.php              # Recruiter dashboard
│   ├── profile.php            # Company settings & logo upload
│   ├── post-job.php           # Publish new vacancies
│   ├── jobs.php               # Manage active drives
│   ├── applicants.php         # Candidate evaluation & status updates
│   ├── interviews.php         # Interview scheduling modal
│   └── analytics.php          # Conversion funnel charts
├── config/
│   ├── config.php             # Session, CSRF, XSS & security helpers
│   └── database.php           # MySQLi connection singleton
├── includes/
│   ├── header.php             # HTML head, Bootstrap, Toast container
│   ├── navbar.php             # Dynamic top navbar & notification dropdown
│   ├── sidebar.php            # Role-based sidebar navigation
│   ├── footer.php             # Footer & JavaScript bundle imports
│   └── auth_check.php         # Role authorization guards
├── assets/
│   ├── css/
│   │   ├── style.css          # Core design system & glassmorphic cards
│   │   └── darkmode.css       # Dark mode theme overrides
│   └── js/
│       ├── main.js            # Validation, dark mode toggle, spinner
│       ├── dashboard.js       # Chart.js initialization
│       └── search.ajax.js     # Live AJAX table & card search
├── uploads/
│   ├── profile/               # Avatar & company logos
│   └── resume/                # Student PDF resumes
├── sql/
│   └── schema.sql             # Complete MariaDB database schema & seeds
├── index.php                  # Public landing page with live counters
├── login.php                  # Multi-role authentication (Admin/Student/Company)
├── register.php               # Student & Company registration
├── logout.php                 # Secure session destruction
├── forgot-password.php        # Password reset handler
├── README.md                  # Project documentation
└── DEPLOYMENT.md              # Amazon Linux 2023 EC2 deployment guide
```

---

## 🚀 Quick Start (Local Setup)

### 1. Database Setup
Ensure MariaDB or MySQL is running on your machine:
```bash
mysql -u root -p < sql/schema.sql
```

### 2. Configure Database Credentials
Edit `config/database.php` if your MariaDB credentials differ from defaults:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'placement_db');
define('DB_PORT', 3306);
```

### 3. Start the Web Server
Using PHP's built-in development server:
```bash
php -S localhost:8000
```
Open your browser and navigate to:
**`http://localhost:8000`**

---

## 🔑 Default Credentials for Testing

| Role | Email | Password |
|---|---|---|
| **Admin** | `admin@placement.com` | `admin123` |
| **Student** | `john.doe@student.edu` | `student123` |
| **Student** | `alice.smith@student.edu` | `student123` |
| **Company** | `hr@apexcloud.tech` | `company123` |
| **Company** | `careers@nexussoft.io` | `company123` |

---

## 🔒 Security Measures

* **SQL Injection Prevention**: 100% prepared statements across all queries.
* **XSS Sanitization**: User inputs sanitized using `htmlspecialchars(ENT_QUOTES, 'UTF-8')`.
* **CSRF Protection**: Unique cryptographic tokens generated per session and validated on all POST transactions.
* **Password Hashing**: Strong bcrypt algorithm via PHP's `password_hash()` and `password_verify()`.
* **Session Protection**: `session.cookie_httponly = 1`, `session.use_only_cookies = 1`, and automatic idle timeout after 30 minutes.
* **File Upload Hardening**: Strict MIME-type checking, extension whitelisting, randomized file renaming, and size restrictions.

---

## 📄 License
This project is open-source under the MIT License.
