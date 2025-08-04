# 🚀 CrowdFunding Platform - Deployment Guide

This comprehensive guide will help you deploy the CrowdFunding Platform to various hosting providers and production environments.

## 📋 Pre-Deployment Checklist

### System Requirements
- **PHP**: 7.4 or higher with required extensions
- **Database**: MySQL 5.7+ or MariaDB 10.2+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **SSL Certificate**: Required for production
- **Domain**: Registered domain name

### Required PHP Extensions
```bash
php -m | grep -E "(pdo|pdo_mysql|json|mbstring|openssl|gd|curl|zip)"
```

## 🌐 Hosting Provider Deployment

### 1. Shared Hosting (cPanel/Plesk)

#### Step 1: Prepare Files
```bash
# Create deployment package
git clone https://github.com/FrancKINANI/CrowdfundingApp.git
cd CrowdfundingApp
zip -r crowdfunding-app.zip . -x "*.git*" "node_modules/*" "tests/*"
```

#### Step 2: Upload Files
1. **Access cPanel File Manager**
2. **Navigate to public_html directory**
3. **Upload and extract** `crowdfunding-app.zip`
4. **Move contents** of `public/` to `public_html/`
5. **Move other directories** (`App/`, `Config/`, etc.) outside `public_html/`

#### Step 3: Configure Environment
```bash
# Create .env file in root directory (outside public_html)
cp .env.example .env
nano .env
```

#### Step 4: Update Paths
Edit `public_html/index.php`:
```php
// Update paths to point outside public_html
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Config/database.php';
```

#### Step 5: Database Setup
1. **Create MySQL database** via cPanel
2. **Create database user** with full privileges
3. **Update .env** with database credentials
4. **Import initial data** (optional)

### 2. VPS/Cloud Hosting (DigitalOcean, AWS, etc.)

#### Step 1: Server Setup
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php7.4 php7.4-mysql php7.4-mbstring php7.4-json php7.4-curl php7.4-gd php7.4-zip -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Certbot for SSL
sudo apt install certbot python3-certbot-apache -y
```

#### Step 2: Deploy Application
```bash
# Clone repository
cd /var/www/
sudo git clone https://github.com/FrancKINANI/CrowdfundingApp.git
sudo chown -R www-data:www-data CrowdfundingApp
cd CrowdfundingApp

# Install dependencies
sudo -u www-data composer install --no-dev --optimize-autoloader

# Set up environment
sudo -u www-data cp .env.example .env
sudo nano .env

# Set permissions
sudo chmod 755 public
sudo chmod -R 755 uploads logs cache
sudo chmod 600 .env
```

#### Step 3: Apache Configuration
```apache
# /etc/apache2/sites-available/crowdfunding.conf
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAlias www.your-domain.com
    DocumentRoot /var/www/CrowdfundingApp/public
    
    <Directory /var/www/CrowdfundingApp/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/crowdfunding_error.log
    CustomLog ${APACHE_LOG_DIR}/crowdfunding_access.log combined
</VirtualHost>
```

```bash
# Enable site and modules
sudo a2ensite crowdfunding.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Step 4: SSL Certificate
```bash
# Get SSL certificate
sudo certbot --apache -d your-domain.com -d www.your-domain.com

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 3. Heroku Deployment

#### Step 1: Prepare for Heroku
Create `Procfile`:
```
web: vendor/bin/heroku-php-apache2 public/
```

Create `composer.json` additions:
```json
{
    "require": {
        "ext-gd": "*"
    },
    "scripts": {
        "post-install-cmd": [
            "php artisan clear-compiled --env=production || true"
        ]
    }
}
```

#### Step 2: Deploy to Heroku
```bash
# Install Heroku CLI and login
heroku login

# Create app
heroku create your-app-name

# Add database
heroku addons:create cleardb:ignite

# Set environment variables
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set APP_URL=https://your-app-name.herokuapp.com

# Deploy
git push heroku main
```

## 🔧 Production Configuration

### Environment Variables (.env)
```env
# Production settings
APP_NAME="Your CrowdFunding Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=UTC

# Database (update with your credentials)
DB_HOST=localhost
DB_PORT=3306
DB_NAME=crowdfunding_prod
DB_USERNAME=crowdfunding_user
DB_PASSWORD=secure_password_here

# Security (generate new keys)
APP_KEY=your-32-character-secret-key-here
SESSION_LIFETIME=7200
CSRF_TOKEN_LIFETIME=3600

# File uploads
MAX_UPLOAD_SIZE=10485760

# Email (configure for notifications)
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="Your Platform Name"

# Logging
LOG_LEVEL=error
LOG_FILE=logs/app.log
```

### Security Hardening

#### 1. File Permissions
```bash
# Set secure permissions
chmod 644 .env
chmod -R 755 public
chmod -R 755 uploads logs cache
chmod -R 644 App Config
find . -name "*.php" -exec chmod 644 {} \;
```

#### 2. Apache Security
Add to `.htaccess`:
```apache
# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Hide sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "*.log">
    Order allow,deny
    Deny from all
</Files>
```

#### 3. Database Security
```sql
-- Create dedicated database user
CREATE USER 'crowdfunding_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON crowdfunding_db.* TO 'crowdfunding_user'@'localhost';
FLUSH PRIVILEGES;
```

## 📊 Monitoring & Maintenance

### 1. Log Monitoring
```bash
# Monitor application logs
tail -f logs/app.log

# Monitor web server logs
tail -f /var/log/apache2/crowdfunding_error.log
```

### 2. Database Backup
```bash
#!/bin/bash
# backup.sh - Daily database backup script
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u crowdfunding_user -p crowdfunding_db > backups/db_backup_$DATE.sql
gzip backups/db_backup_$DATE.sql

# Keep only last 7 days
find backups/ -name "db_backup_*.sql.gz" -mtime +7 -delete
```

### 3. Performance Optimization
```bash
# Enable PHP OPcache
echo "opcache.enable=1" >> /etc/php/7.4/apache2/php.ini
echo "opcache.memory_consumption=128" >> /etc/php/7.4/apache2/php.ini

# Enable Apache compression
sudo a2enmod deflate
sudo systemctl restart apache2
```

## 🔍 Troubleshooting

### Common Issues

#### 1. File Upload Issues
```bash
# Check PHP upload settings
php -i | grep -E "(upload_max_filesize|post_max_size|max_execution_time)"

# Fix permissions
chmod 755 uploads/
chown -R www-data:www-data uploads/
```

#### 2. Database Connection Issues
```bash
# Test database connection
mysql -u crowdfunding_user -p -h localhost crowdfunding_db

# Check PHP MySQL extension
php -m | grep mysql
```

#### 3. SSL Certificate Issues
```bash
# Check certificate status
sudo certbot certificates

# Renew certificate
sudo certbot renew --dry-run
```

### Performance Issues
```bash
# Check server resources
htop
df -h
free -m

# Optimize MySQL
sudo mysql_secure_installation
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

## 📞 Support & Resources

### Documentation
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Apache Documentation](https://httpd.apache.org/docs/)

### Hosting Providers
- **Shared Hosting**: Bluehost, SiteGround, HostGator
- **VPS/Cloud**: DigitalOcean, Linode, AWS EC2
- **Platform-as-a-Service**: Heroku, Platform.sh

### Monitoring Tools
- **Uptime**: UptimeRobot, Pingdom
- **Performance**: New Relic, DataDog
- **Security**: Sucuri, Cloudflare

---

**Need Help?** Contact support or check the [GitHub Issues](https://github.com/FrancKINANI/CrowdfundingApp/issues) for community assistance.
