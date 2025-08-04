# 🚀 Production Deployment Guide - SaaS CrowdFunding Platform

## Overview
This guide covers the complete deployment of the transformed SaaS crowdfunding platform to production environments with enterprise-grade features, security, and scalability.

## Prerequisites

### System Requirements
- **Server**: 4+ CPU cores, 8GB+ RAM, 100GB+ SSD
- **Operating System**: Ubuntu 20.04 LTS or newer
- **Docker**: 20.10+ with Docker Compose v2
- **Domain**: Registered domain with DNS access
- **SSL Certificate**: Let's Encrypt or commercial SSL
- **External Services**: Stripe account, email service (SendGrid/Mailgun)

### Required Environment Variables
Create a `.env.production` file with the following variables:

```bash
# Application
APP_NAME="CrowdFund Pro"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_KEY=your-32-character-secret-key-here
APP_TIMEZONE=UTC

# Database
DB_HOST=mysql
DB_PORT=3306
DB_NAME=crowdfunding_prod
DB_USERNAME=crowdfunding_user
DB_PASSWORD=secure_database_password
MYSQL_ROOT_PASSWORD=secure_root_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=secure_redis_password

# Stripe Payment Processing
STRIPE_SECRET_KEY=sk_live_your_stripe_secret_key
STRIPE_PUBLISHABLE_KEY=pk_live_your_stripe_publishable_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret

# Email Configuration
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="CrowdFund Pro"

# Domain and SSL
DOMAIN=your-domain.com
SSL_EMAIL=admin@your-domain.com

# Monitoring
GRAFANA_PASSWORD=secure_grafana_password
SENTRY_SECRET_KEY=your_sentry_secret_key
SENTRY_DB_PASSWORD=secure_sentry_password

# Security
SESSION_LIFETIME=7200
CSRF_TOKEN_LIFETIME=3600
API_RATE_LIMIT=100

# File Upload
MAX_UPLOAD_SIZE=10485760
ALLOWED_IMAGE_TYPES=jpg,jpeg,png,gif,webp

# Cache
CACHE_ENABLED=true
CACHE_LIFETIME=3600

# Logging
LOG_LEVEL=error
LOG_CHANNEL=stack
```

## Deployment Steps

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo usermod -aG docker $USER

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Create application directory
sudo mkdir -p /var/www/crowdfunding-production
sudo chown $USER:$USER /var/www/crowdfunding-production
cd /var/www/crowdfunding-production
```

### 2. Application Deployment

```bash
# Clone repository
git clone https://github.com/FrancKINANI/CrowdfundingApp.git .

# Copy production environment
cp .env.example .env.production
# Edit .env.production with your values

# Create required directories
mkdir -p uploads logs cache docker/nginx/ssl

# Set permissions
chmod 755 uploads logs cache
chmod 600 .env.production

# Build and start services
docker-compose -f docker-compose.prod.yml --env-file .env.production up -d

# Wait for services to start
sleep 30

# Run database migrations
docker-compose -f docker-compose.prod.yml exec app php Config/migrate.php

# Create admin user
docker-compose -f docker-compose.prod.yml exec app php scripts/create-admin.php
```

### 3. SSL Certificate Setup

```bash
# Initial certificate generation
docker-compose -f docker-compose.prod.yml run --rm certbot

# Set up automatic renewal
echo "0 12 * * * /usr/local/bin/docker-compose -f /var/www/crowdfunding-production/docker-compose.prod.yml run --rm certbot renew --quiet" | sudo crontab -
```

### 4. Nginx Configuration

Create `docker/nginx/prod.conf`:

```nginx
events {
    worker_connections 1024;
}

http {
    upstream app {
        server app:80;
    }

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

    # SSL redirect
    server {
        listen 80;
        server_name your-domain.com www.your-domain.com;
        return 301 https://$server_name$request_uri;
    }

    # Main server
    server {
        listen 443 ssl http2;
        server_name your-domain.com www.your-domain.com;

        # SSL configuration
        ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
        ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
        ssl_protocols TLSv1.2 TLSv1.3;
        ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
        ssl_prefer_server_ciphers off;

        # Security headers
        add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
        add_header X-Frame-Options DENY always;
        add_header X-Content-Type-Options nosniff always;
        add_header X-XSS-Protection "1; mode=block" always;

        # Gzip compression
        gzip on;
        gzip_vary on;
        gzip_min_length 1024;
        gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;

        # Rate limiting
        location /api/ {
            limit_req zone=api burst=20 nodelay;
            proxy_pass http://app;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }

        location /login {
            limit_req zone=login burst=5 nodelay;
            proxy_pass http://app;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }

        # Static files
        location /uploads/ {
            alias /var/www/html/uploads/;
            expires 1y;
            add_header Cache-Control "public, immutable";
        }

        # Main application
        location / {
            proxy_pass http://app;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
        }
    }
}
```

### 5. Database Backup Setup

Create `scripts/backup.sh`:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups"
DB_NAME="crowdfunding_prod"

# Create backup
mysqldump -h mysql -u $MYSQL_USER -p$MYSQL_PASSWORD $DB_NAME > $BACKUP_DIR/backup_$DATE.sql

# Compress backup
gzip $BACKUP_DIR/backup_$DATE.sql

# Upload to S3 (optional)
# aws s3 cp $BACKUP_DIR/backup_$DATE.sql.gz s3://your-backup-bucket/

# Keep only last 7 days
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete

echo "Backup completed: backup_$DATE.sql.gz"
```

Set up daily backups:
```bash
echo "0 2 * * * docker-compose -f /var/www/crowdfunding-production/docker-compose.prod.yml run --rm db-backup" | sudo crontab -
```

### 6. Monitoring Setup

Access monitoring dashboards:
- **Grafana**: https://your-domain.com:3000 (admin/your-grafana-password)
- **Kibana**: https://your-domain.com:5601
- **Prometheus**: https://your-domain.com:9090

### 7. Health Checks

Create health check endpoint and monitoring:

```bash
# Test application health
curl -f https://your-domain.com/health

# Check all services
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f app
```

## Security Hardening

### 1. Firewall Configuration

```bash
# Install UFW
sudo apt install ufw

# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH
sudo ufw allow ssh

# Allow HTTP/HTTPS
sudo ufw allow 80
sudo ufw allow 443

# Allow monitoring (restrict to your IP)
sudo ufw allow from YOUR_IP_ADDRESS to any port 3000
sudo ufw allow from YOUR_IP_ADDRESS to any port 5601
sudo ufw allow from YOUR_IP_ADDRESS to any port 9090

# Enable firewall
sudo ufw enable
```

### 2. Fail2Ban Setup

```bash
# Install Fail2Ban
sudo apt install fail2ban

# Configure for Nginx
sudo tee /etc/fail2ban/jail.local << EOF
[nginx-http-auth]
enabled = true
filter = nginx-http-auth
logpath = /var/log/nginx/error.log
maxretry = 3
bantime = 3600

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
logpath = /var/log/nginx/error.log
maxretry = 10
bantime = 600
EOF

sudo systemctl restart fail2ban
```

### 3. Regular Security Updates

```bash
# Create update script
sudo tee /usr/local/bin/security-updates.sh << EOF
#!/bin/bash
apt update
apt upgrade -y
docker system prune -f
EOF

sudo chmod +x /usr/local/bin/security-updates.sh

# Schedule weekly updates
echo "0 3 * * 0 /usr/local/bin/security-updates.sh" | sudo crontab -
```

## Performance Optimization

### 1. Database Optimization

```sql
-- Add to docker/mysql/prod.cnf
[mysqld]
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 128M
query_cache_type = 1
max_connections = 200
```

### 2. Redis Configuration

```bash
# Optimize Redis for production
echo "maxmemory-policy allkeys-lru" >> docker/redis/redis.conf
echo "save 900 1" >> docker/redis/redis.conf
echo "save 300 10" >> docker/redis/redis.conf
```

### 3. PHP Optimization

```ini
; Add to docker/php/prod.ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

## Maintenance

### Daily Tasks
- Monitor application logs
- Check system resources
- Verify backup completion
- Review security alerts

### Weekly Tasks
- Update system packages
- Review performance metrics
- Analyze user activity
- Check SSL certificate expiry

### Monthly Tasks
- Security audit
- Performance optimization
- Database maintenance
- Disaster recovery testing

## Troubleshooting

### Common Issues

1. **High Memory Usage**
   ```bash
   # Check container resources
   docker stats
   
   # Optimize MySQL
   docker-compose exec mysql mysql_tuner
   ```

2. **Slow Response Times**
   ```bash
   # Check application logs
   docker-compose logs app
   
   # Monitor database queries
   docker-compose exec mysql mysqladmin processlist
   ```

3. **SSL Certificate Issues**
   ```bash
   # Renew certificate manually
   docker-compose run --rm certbot renew
   
   # Check certificate status
   openssl x509 -in /etc/letsencrypt/live/your-domain.com/cert.pem -text -noout
   ```

## Support

For technical support and maintenance:
- Monitor logs: `docker-compose logs -f`
- Check health: `curl https://your-domain.com/health`
- Database access: `docker-compose exec mysql mysql -u root -p`
- Application shell: `docker-compose exec app bash`

## Scaling

For high-traffic scenarios:
1. Add more application replicas
2. Implement database read replicas
3. Use external Redis cluster
4. Add CDN for static assets
5. Implement horizontal pod autoscaling
