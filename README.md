# Placement Management System

A web-based Placement Management System designed to organize campus recruitment by bringing students, company recruiters, and administrators together through role-based portals.

**Live website:** https://cloudwithhashir.in/

## Overview

The application provides separate workflows for students, recruiters, and administrators. Students can manage their profiles, browse vacancies, submit applications, and review interview information. Recruiters can publish vacancies and review applicants. Administrators can manage placement records and view reports.

## Features

### Student portal
- Manage profile and resume information
- Browse job vacancies and apply
- Track submitted applications
- View interview schedules

### Company portal
- Post and manage job vacancies
- Review candidate applications
- Manage recruitment activities
- Schedule interviews

### Admin portal
- Manage student and company records
- Oversee job postings and applications
- Manage interview information
- View placement statistics and reports
- Export placement data where supported by the application

## Application Screenshots

### Login Page
![Placement Management System Login](screenshots/login-page.png)

### Student Dashboard
![Student Dashboard](screenshots/student-dashboard.png)

### Company Dashboard
![Company Recruiter Dashboard](screenshots/company-dashboard.png)

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)

### Placement Reports
![Placement Reports](screenshots/placement-reports.png)

## Logical Architecture

![Placement Management System architecture illustration](architecture/placement-management-architecture.png)

The diagram is an illustrative overview. Verify the deployed infrastructure and services before treating any cloud-specific elements in the diagram as confirmed implementation details.

## Technology Stack

The page routes use PHP-style filenames. Confirm the repository's source code before finalizing the complete technology list. The likely application components include:

- PHP for server-side application logic
- HTML, CSS, and JavaScript for the user interface
- MySQL, if used as the application's database

## Main Modules

| Module | Purpose |
|---|---|
| Student management | Maintains student profiles and placement information |
| Company management | Organizes company and recruiter information |
| Job management | Handles vacancy details |
| Applications | Tracks student job applications |
| Interviews | Manages interview details and schedules |
| Reports | Presents placement statistics and report exports |

## Application Entry Points

- Login: `https://cloudwithhashir.in/login.php`
- Student portal: `/student/index.php`
- Company portal: `/company/index.php`
- Admin portal: `/admin/index.php`
- Reports: `/admin/reports.php`

Access to each portal depends on the application's login and role permissions.

## Future Enhancements

- Email notifications for application and interview updates
- Improved job search and filtering
- Additional placement analytics
- Stronger security controls and audit logging
- Responsive improvements for mobile devices
- Automated deployment and cloud monitoring

## Author

**Mohammed Hashir**  
GitHub: [mdhashir05](https://github.com/mdhashir05)

---

> Before publishing, review all screenshots for sample personal information or sensitive details. Keep the image filenames and folder structure unchanged, or update the paths above to match your repository.
