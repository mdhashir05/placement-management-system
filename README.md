# Placement Management System on AWS

A web-based Placement Management System deployed on **Amazon Web Services (AWS)** to bring students, company recruiters, and administrators together on one platform for managing campus recruitment activities.

## Overview

The application provides separate role-based portals for students, companies, and administrators. It supports job vacancy management, student applications, interview coordination, and placement reporting.

The application is hosted on an **Amazon EC2 instance** running Nginx as the web server, with MariaDB used for database management. **Amazon Route 53** is configured to manage DNS records for the custom domain `cloudwithhashir.in`.

This project demonstrates practical experience in AWS cloud hosting, Linux server administration, web server configuration, database integration, and DNS management.

## Features

### Student Portal
- Manage student profiles and resume information.
- Browse job opportunities and apply for vacancies.
- Track applications and view interview information.

### Company Portal
- Post and manage job vacancies.
- Review student applications.
- Coordinate interview schedules.

### Admin Portal
- Manage student and company records.
- Oversee jobs, applications, and interviews.
- Review placement statistics and reports.
- Export placement report data.

## AWS Infrastructure and Technology Stack

- **Amazon EC2:** Hosts the web application on a cloud-based virtual server.
- **Nginx:** Serves the web application and handles incoming HTTP requests.
- **MariaDB:** Stores and manages application data.
- **Amazon Route 53:** Manages DNS records for the custom domain.
- **PHP:** Implements the application's server-side functionality.
- **HTML, CSS, and JavaScript:** Support the application's user interface.

## Project Highlights

- Deployed a PHP-based web application on Amazon EC2.
- Configured Nginx to serve the application.
- Integrated MariaDB for database operations.
- Configured DNS records through Amazon Route 53.
- Implemented separate portals for students, companies, and administrators.
- Documented the application interface and architecture for a cloud portfolio.



## Application Screenshots

The following images show the application's main pages. Ensure the corresponding image files are uploaded to the repository's `screenshots/` folder so GitHub can render them here.

### Login Page
![Placement Management System login page](screenshots/screenshot-01.png)

### Student Portal
![Placement Management System student portal](screenshots/screenshot-02.png)

### Company Portal
![Placement Management System company portal](screenshots/screenshot-03.png)

### Admin Portal
![Placement Management System admin portal](screenshots/screenshot-04.png)

### Additional Application Views

![Placement Management System screenshot 5](screenshots/screenshot-05.png)

![Placement Management System screenshot 6](screenshots/screenshot-06.png)

![Placement Management System screenshot 7](screenshots/screenshot-07.png)

![Placement Management System screenshot 8](screenshots/screenshot-08.png)

![Placement Management System screenshot 9](screenshots/screenshot-09.png)

![Placement Management System screenshot 10](screenshots/screenshot-10.png)

## Domain and DNS Configuration

Amazon Route 53 was used to configure a public hosted zone for the custom domain `cloudwithhashir.in`. The screenshot shows the hosted zone's DNS records, including the A, NS, and SOA records.

![Amazon Route 53 hosted zone and DNS records](screenshots/route53-dns-configuration.png)

**AWS service:** Amazon Route 53  
**Domain:** `cloudwithhashir.in`  
**Configuration shown:** Public hosted zone with A, NS, and SOA records

*Note: A DNS record screenshot documents configuration, but does not by itself confirm that the website is currently reachable. DNS changes and domain registration/delegation must also be correctly configured.*

## Architecture

The diagram below provides a high-level logical view of the placement management application. Confirm that it matches the actual implementation before presenting it as a deployed cloud architecture.

![Placement Management System architecture](placement-management-architecture.png)

## Technology Stack

The application uses PHP-based page routes. Confirm the source code and database configuration before finalizing the complete technology stack. Include HTML, CSS, JavaScript, PHP, and MySQL only where they match the actual implementation.

## Application Routes

- Login: [https://cloudwithhashir.in/login.php](https://cloudwithhashir.in/login.php)
- Student portal: `/student/index.php`
- Company portal: `/company/index.php`
- Admin portal: `/admin/index.php`
- Admin reports: `/admin/reports.php`

Portal access depends on the user's role and the application's authentication and authorization rules.

## Future Enhancements

- **Amazon SES:** Email notifications for applications and interviews.
- **Amazon CloudWatch:** Monitor EC2 performance and application logs.
- **Amazon S3:** Store resumes and uploaded documents.
- **AWS CodeDeploy:** Automate application deployments.
- **Enhanced Security:** Configure HTTPS and strengthen AWS access controls.
- **Scalability:** Use a load balancer and managed database as the application grows.

## Author

**Mohammed Hashir**

GitHub: [mdhashir05](https://github.com/mdhashir05)
