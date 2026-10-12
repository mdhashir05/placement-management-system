# AWS EC2 (Amazon Linux 2023) Production Deployment Guide

This guide details step-by-step instructions for deploying the **Placement Management System** on an AWS EC2 instance running **Amazon Linux 2023** with **Nginx**, **PHP 8.2/8.3-FPM**, and **MariaDB 10.x**.

---

## 1. Launch EC2 Instance & Connect

1. In AWS Console, launch an **Amazon Linux 2023 AMI** (t3.micro or t3.small).
2. Configure **Security Group** Inbound rules:
   * **SSH**: Port 22 (Your IP)
   * **HTTP**: Port 80 (Anywhere `0.0.0.0/0`)
   * **HTTPS**: Port 443 (Anywhere `0.0.0.0/0`)
3. Connect to your instance via SSH:
   ```bash
   ssh -i your-key.pem ec2-user@your-ec2-public-ip
   ```

---

## 2. Update System & Install Stack

```bash
# Update repository packages
sudo dnf update -y

# Install Nginx web server
sudo dnf install -y nginx

# Install MariaDB Server
sudo dnf install -y mariadb105-server

# Install PHP 8.2 and required PHP extensions
sudo dnf install -y php8.2 php8.2-fpm php8.2-mysqlnd php8.2-mbstring php8.2-xml php8.2-gd php8.2-curl php8.2-zip

# Install Git
sudo dnf install -y git
```

---

## 3. Configure and Start MariaDB

```bash
# Start and enable MariaDB service
sudo systemctl enable --now mariadb

# Secure MariaDB installation
sudo mysql_secure_installation
# (Set root password, remove test database and anonymous users)

# Create Database and Dedicated User
sudo mysql -u root -p
```

Inside the MariaDB shell:
```sql
CREATE DATABASE placement_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'placement_user'@'localhost' IDENTIFIED BY 'StrongPassword123#';
GRANT ALL PRIVILEGES ON placement_db.* TO 'placement_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## 4. Deploy Application Code

```bash
# Navigate to web root
cd /var/www

# Clone or transfer the project
sudo git clone https://github.com/your-repo/placement-management-system.git html
# OR copy files to /var/www/html

cd /var/www/html

# Import MariaDB Database Schema
mysql -u placement_user -p placement_db < sql/schema.sql

# Configure Database Credentials
sudo cp config/database.php config/database.php.bak
sudo nano config/database.php
```

Update `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'placement_user');
define('DB_PASS', 'StrongPassword123#');
define('DB_NAME', 'placement_db');
define('DB_PORT', 3306);
```

---

## 5. Set File Permissions & Directory Ownership

Ensure Nginx and PHP-FPM (`nginx` user on Amazon Linux 2023) have appropriate permissions:
```bash
# Ensure upload folders exist
sudo mkdir -p /var/www/html/uploads/profile /var/www/html/uploads/resume

# Set ownership to nginx user
sudo chown -R nginx:nginx /var/www/html

# Set directory and file permissions
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;

# Allow write access to uploads folder
sudo chmod -R 775 /var/www/html/uploads
```

---

## 6. Configure PHP-FPM

Edit the PHP-FPM www pool configuration:
```bash
sudo nano /etc/php-fpm.d/www.conf
```
Verify or update the following parameters:
```ini
user = nginx
group = nginx
listen = /run/php-fpm/www.sock
listen.owner = nginx
listen.group = nginx
listen.mode = 0660
```

Start and enable PHP-FPM:
```bash
sudo systemctl enable --now php-fpm
```

---

## 7. Configure Nginx Server Block

Create a new virtual host configuration:
```bash
sudo nano /etc/nginx/conf.d/placement.conf
```

Add the following configuration:
```nginx
server {
    listen 80;
    server_name your-ec2-public-ip-or-domain;
    root /var/www/html;
    index index.php index.html;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    # Pass PHP scripts to PHP-FPM unix socket
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    # Protect sensitive config and SQL files
    location ~* /(config|sql|\.git)/ {
        deny all;
        return 404;
    }

    # Cache static assets
    location ~* \.(css|js|png|jpg|jpeg|gif|svg|ico)$ {
        expires 30d;
        add_header Cache-Control "public, no-transform";
    }
}
```

Test and start Nginx:
```bash
sudo nginx -t
sudo systemctl enable --now nginx
```

---

## 8. Configure Free SSL / HTTPS (Let's Encrypt Certbot)

If pointing a domain to your EC2 instance:
```bash
# Install Certbot
sudo dnf install -y certbot python3-certbot-nginx

# Obtain and install SSL Certificate
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

---

## 9. Verification & Service Health Check

Verify all services are active:
```bash
sudo systemctl status nginx
sudo systemctl status php-fpm
sudo systemctl status mariadb
```

Open your browser at:
`http://your-ec2-public-ip/`
Log in with:
* **Admin**: `admin@placement.com` / `admin123`
* **Student**: `john.doe@student.edu` / `student123`
* **Company**: `hr@apexcloud.tech` / `company123`
