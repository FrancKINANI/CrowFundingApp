# 🚀 CrowdFunding Platform

[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE)
[![Security](https://img.shields.io/badge/Security-Enhanced-brightgreen.svg)](#security-features)

A modern, secure crowdfunding platform built with PHP, featuring enterprise-grade security, comprehensive validation, and production-ready deployment capabilities.

## ✨ Features

### Core Functionality
- 🔐 **Secure User Authentication** - Registration, login, logout with session management
- 📊 **Project Management** - Create, view, edit, and delete crowdfunding projects
- 💰 **Donation System** - Contribute to projects with real-time progress tracking
- 📈 **Dashboard** - User dashboard with project and donation analytics
- 📱 **Responsive Design** - Mobile-first, Bootstrap-powered interface

### Security Features
- 🛡️ **CSRF Protection** - Cross-Site Request Forgery prevention
- 🔒 **Input Validation & Sanitization** - Comprehensive server-side validation
- 🚫 **Rate Limiting** - Brute force attack prevention
- 🔑 **Password Security** - Strong password requirements and hashing
- 📝 **Security Logging** - Comprehensive audit trail
- 🌐 **Security Headers** - XSS, clickjacking, and MIME-type protection

### Production Features
- ⚙️ **Environment Configuration** - Flexible configuration management
- 📊 **Error Handling** - Comprehensive error logging and handling
- 🚀 **Performance Optimization** - Caching and compression support
- 📦 **Composer Integration** - Modern dependency management
- 🔧 **Deployment Scripts** - Automated deployment and setup

## Project Structure
### Folder Organization
```
CrowdfundingApp/
├── App/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── ProjectController.php
│   │   ├── HomeController.php
│   │   ├── DonationController.php
│   │   ├── UserController.php
│   │   └── Router.php
│   ├── Models/
│   │   ├── User.php
│   │   └── Project.php
│   │   └── Donation.php
│   └── Views/
│       ├── auth/
│       │   ├── login.php
│       │   └── register.php
│       ├── projects/
│       │   ├── create.php
│       │   ├── edit.php
│       │   └── view.php
│       └── donations/
│       |   ├── create.php
│       │   ├── edit.php
│       │   └── view.php
│       ├── home.php
│       └── layout.php
├── Config/
│   └── database.php
├── public/
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   └── app.js
│   └── index.php
└── README.md
```

---

## 📋 Requirements

### System Requirements
- **PHP**: 7.4 or higher
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Extensions**: PDO, PDO_MySQL, JSON, mbstring, OpenSSL

### Development Requirements
- **Composer**: For dependency management
- **Git**: For version control

---

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/FrancKINANI/CrowdfundingApp.git
   cd CrowdfundingApp
   ```

2. **Run the deployment script:**
   ```bash
   chmod +x deploy.sh
   ./deploy.sh
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   nano .env
   ```

4. **Start development server:**
   ```bash
   php -S localhost:8000 -t public
   ```

### Option 2: Manual Setup

1. **Clone and install dependencies:**
   ```bash
   git clone https://github.com/FrancKINANI/CrowdfundingApp.git
   cd CrowdfundingApp
   composer install
   ```

2. **Environment setup:**
   ```bash
   cp .env.example .env
   # Configure your database settings in .env
   ```

3. **Create directories:**
   ```bash
   mkdir -p logs uploads cache
   chmod 755 logs uploads cache
   ```

4. **Database setup:**
   - Create a MySQL database
   - Update `.env` with your database credentials
   - The application will create tables automatically

5. **Web server configuration:**
   - Point your web server document root to the `public` directory
   - Ensure `.htaccess` files are processed (Apache) or configure URL rewriting (Nginx)

---

## 📖 Usage Guide

### Getting Started
1. **🔐 Create Account**: Register with your email and a secure password
2. **🚪 Login**: Access your dashboard with your credentials
3. **📊 Dashboard**: View your projects and donation history
4. **💡 Create Projects**: Launch your crowdfunding campaigns
5. **💰 Contribute**: Support projects you believe in
6. **📈 Track Progress**: Monitor funding progress in real-time

### User Roles
- **Project Creators**: Can create, edit, and manage their projects
- **Contributors**: Can donate to projects and track their contributions
- **Administrators**: Full system access (future enhancement)

---

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+**: Modern PHP with OOP principles
- **MySQL**: Reliable database with proper relationships
- **PDO**: Secure database abstraction layer

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling with Bootstrap 5
- **JavaScript**: Enhanced user experience
- **Bootstrap 5**: Responsive, mobile-first design

### Security & Performance
- **CSRF Protection**: Request forgery prevention
- **Input Validation**: Server-side validation and sanitization
- **Rate Limiting**: Brute force protection
- **Security Headers**: XSS and clickjacking prevention
- **Error Handling**: Comprehensive logging and monitoring

---

## 🔧 Configuration

### Environment Variables
Key configuration options in `.env`:

```env
# Application
APP_NAME="CrowdFunding Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_HOST=localhost
DB_NAME=crowdfunding_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Security
APP_KEY=your-32-character-secret-key
SESSION_LIFETIME=7200
```

### Web Server Configuration

#### Apache (.htaccess included)
The application includes `.htaccess` files for Apache configuration.

#### Nginx
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/crowdfunding/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

---

## 🚀 Deployment

### Production Deployment

1. **Server Setup:**
   ```bash
   # Update system
   sudo apt update && sudo apt upgrade -y

   # Install required packages
   sudo apt install php7.4 php7.4-mysql php7.4-mbstring php7.4-json php7.4-curl
   sudo apt install mysql-server nginx
   ```

2. **Application Deployment:**
   ```bash
   # Clone repository
   git clone https://github.com/FrancKINANI/CrowdfundingApp.git
   cd CrowdfundingApp

   # Run deployment script
   ./deploy.sh

   # Configure environment for production
   nano .env
   ```

3. **Security Hardening:**
   - Enable HTTPS/SSL certificates
   - Configure firewall (UFW)
   - Set up regular backups
   - Monitor application logs
   - Keep system updated

### Docker Deployment (Optional)
```dockerfile
FROM php:7.4-apache
COPY . /var/www/html/
RUN docker-php-ext-install pdo pdo_mysql
EXPOSE 80
```

---

## 🔒 Security Features

### Authentication & Authorization
- Secure password hashing (bcrypt)
- Session management with regeneration
- CSRF token validation
- Rate limiting on sensitive endpoints

### Input Validation
- Server-side validation for all inputs
- SQL injection prevention with prepared statements
- XSS protection with output encoding
- File upload validation and restrictions

### Security Headers
- X-Frame-Options: DENY
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Content-Security-Policy
- Strict-Transport-Security (HTTPS)

---

## 🧪 Testing

### Manual Testing
1. **Authentication Flow**: Registration, login, logout
2. **Project Management**: Create, edit, delete projects
3. **Donation Process**: Make donations, view history
4. **Security Testing**: CSRF, XSS, SQL injection attempts

### Automated Testing (Future Enhancement)
```bash
# Run PHPUnit tests
composer test
```

---

## 📊 Monitoring & Logging

### Application Logs
- Location: `logs/app.log`
- Levels: Emergency, Alert, Critical, Error, Warning, Notice, Info, Debug
- Security events logged automatically

### Performance Monitoring
- Database query logging (development)
- Error tracking and reporting
- User activity logging

---

## 🔮 Future Enhancements

### Planned Features
- **🔐 Advanced User Roles**: Admin, Moderator, Creator, Contributor
- **🔍 Search & Filtering**: Advanced project discovery
- **📧 Email Notifications**: Project updates and milestones
- **💳 Payment Integration**: Stripe, PayPal, cryptocurrency
- **📱 Mobile App**: React Native or Flutter app
- **🌍 Internationalization**: Multi-language support
- **📊 Analytics Dashboard**: Advanced reporting and insights
- **🤖 API Development**: RESTful API for third-party integrations

### Technical Improvements
- **🧪 Unit Testing**: Comprehensive test coverage
- **🔄 CI/CD Pipeline**: Automated testing and deployment
- **📦 Microservices**: Service-oriented architecture
- **🚀 Performance**: Redis caching, CDN integration
- **🔍 Search Engine**: Elasticsearch integration

---

## License
This project is open-source and available under the [MIT License](LICENSE).

---

## Contact
For any inquiries, please contact:
- **Name**: David J.
- **Email**: fkinaninkaya@gmail.com
- **GitHub**: https://github.com/FrancKINANI/
