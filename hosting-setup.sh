#!/bin/bash

# CrowdFunding Platform - Hosting Setup Script
# This script helps set up the application on various hosting environments

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

print_header() {
    echo -e "${BLUE}[SETUP]${NC} $1"
}

# Display banner
echo "
╔══════════════════════════════════════════════════════════════╗
║                 CrowdFunding Platform Setup                 ║
║                   Production Deployment                     ║
╚══════════════════════════════════════════════════════════════╝
"

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons"
   print_status "Please run as a regular user with sudo privileges"
   exit 1
fi

# Detect hosting environment
detect_environment() {
    if [ -d "/home" ] && [ -d "/var/www" ]; then
        echo "vps"
    elif [ -d "/public_html" ] || [ -d "~/public_html" ]; then
        echo "shared"
    elif [ ! -z "$DYNO" ]; then
        echo "heroku"
    else
        echo "unknown"
    fi
}

HOSTING_ENV=$(detect_environment)
print_status "Detected hosting environment: $HOSTING_ENV"

# Function to check PHP version and extensions
check_php() {
    print_header "Checking PHP Requirements"
    
    if ! command -v php &> /dev/null; then
        print_error "PHP is not installed"
        return 1
    fi
    
    PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
    print_status "PHP version: $PHP_VERSION"
    
    if [[ $(echo "$PHP_VERSION >= 7.4" | bc -l 2>/dev/null || echo "0") -eq 0 ]]; then
        print_error "PHP 7.4 or higher is required. Current version: $PHP_VERSION"
        return 1
    fi
    
    # Check required extensions
    REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "json" "mbstring" "openssl" "gd" "curl")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "^$ext$"; then
            print_error "Required PHP extension '$ext' is not installed"
            return 1
        fi
    done
    
    print_status "All PHP requirements satisfied"
    return 0
}

# Function to setup for VPS/Cloud hosting
setup_vps() {
    print_header "Setting up for VPS/Cloud hosting"
    
    # Check if we have sudo access
    if ! sudo -n true 2>/dev/null; then
        print_error "This script requires sudo access for VPS setup"
        exit 1
    fi
    
    # Install dependencies if needed
    print_status "Installing system dependencies..."
    sudo apt update
    
    # Install Apache if not present
    if ! command -v apache2 &> /dev/null; then
        print_status "Installing Apache..."
        sudo apt install -y apache2
    fi
    
    # Install MySQL if not present
    if ! command -v mysql &> /dev/null; then
        print_status "Installing MySQL..."
        sudo apt install -y mysql-server
    fi
    
    # Install PHP and extensions if needed
    if ! check_php; then
        print_status "Installing PHP and extensions..."
        sudo apt install -y php7.4 php7.4-mysql php7.4-mbstring php7.4-json php7.4-curl php7.4-gd php7.4-zip libapache2-mod-php7.4
    fi
    
    # Install Composer if not present
    if ! command -v composer &> /dev/null; then
        print_status "Installing Composer..."
        curl -sS https://getcomposer.org/installer | php
        sudo mv composer.phar /usr/local/bin/composer
    fi
    
    # Set up application directory
    APP_DIR="/var/www/crowdfunding"
    if [ ! -d "$APP_DIR" ]; then
        print_status "Creating application directory..."
        sudo mkdir -p $APP_DIR
        sudo chown -R $USER:www-data $APP_DIR
    fi
    
    # Copy files
    print_status "Copying application files..."
    cp -r . $APP_DIR/
    cd $APP_DIR
    
    # Install Composer dependencies
    print_status "Installing Composer dependencies..."
    composer install --no-dev --optimize-autoloader
    
    # Set up environment
    if [ ! -f ".env" ]; then
        print_status "Setting up environment configuration..."
        cp .env.example .env
        
        # Generate app key
        APP_KEY=$(openssl rand -hex 32)
        sed -i "s/APP_KEY=your-secret-key-here-change-in-production/APP_KEY=$APP_KEY/" .env
        
        print_warning "Please edit .env file with your database and domain settings"
    fi
    
    # Set permissions
    print_status "Setting file permissions..."
    sudo chown -R www-data:www-data $APP_DIR
    sudo chmod -R 755 $APP_DIR/public
    sudo chmod -R 755 $APP_DIR/uploads $APP_DIR/logs $APP_DIR/cache
    sudo chmod 600 $APP_DIR/.env
    
    # Create Apache virtual host
    create_apache_vhost $APP_DIR
    
    print_status "VPS setup completed!"
    print_warning "Don't forget to:"
    print_warning "1. Configure your database in .env"
    print_warning "2. Set up SSL certificate with: sudo certbot --apache -d your-domain.com"
    print_warning "3. Configure DNS to point to this server"
}

# Function to create Apache virtual host
create_apache_vhost() {
    local app_dir=$1
    local domain=""
    
    read -p "Enter your domain name (e.g., crowdfunding.example.com): " domain
    
    if [ -z "$domain" ]; then
        print_warning "No domain provided, skipping virtual host creation"
        return
    fi
    
    print_status "Creating Apache virtual host for $domain..."
    
    sudo tee /etc/apache2/sites-available/crowdfunding.conf > /dev/null <<EOF
<VirtualHost *:80>
    ServerName $domain
    ServerAlias www.$domain
    DocumentRoot $app_dir/public
    
    <Directory $app_dir/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog \${APACHE_LOG_DIR}/crowdfunding_error.log
    CustomLog \${APACHE_LOG_DIR}/crowdfunding_access.log combined
</VirtualHost>
EOF
    
    # Enable site and modules
    sudo a2ensite crowdfunding.conf
    sudo a2enmod rewrite
    sudo systemctl restart apache2
    
    print_status "Virtual host created for $domain"
}

# Function to setup for shared hosting
setup_shared() {
    print_header "Setting up for Shared Hosting"
    
    print_status "Preparing files for shared hosting upload..."
    
    # Create deployment package
    DEPLOY_DIR="crowdfunding-deploy"
    mkdir -p $DEPLOY_DIR
    
    # Copy public files to root of deploy directory
    cp -r public/* $DEPLOY_DIR/
    
    # Copy other directories
    cp -r App Config $DEPLOY_DIR/
    cp .env.example $DEPLOY_DIR/.env
    cp .htaccess $DEPLOY_DIR/
    
    # Update paths in index.php for shared hosting
    sed -i 's|__DIR__ . "/../|__DIR__ . "/|g' $DEPLOY_DIR/index.php
    
    # Create zip file
    zip -r crowdfunding-shared-hosting.zip $DEPLOY_DIR/
    
    print_status "Deployment package created: crowdfunding-shared-hosting.zip"
    print_warning "Upload instructions:"
    print_warning "1. Extract the zip file contents to your public_html directory"
    print_warning "2. Move App/ and Config/ directories outside public_html for security"
    print_warning "3. Update paths in index.php if needed"
    print_warning "4. Configure .env with your database settings"
    print_warning "5. Create MySQL database via cPanel/Plesk"
    
    # Clean up
    rm -rf $DEPLOY_DIR
}

# Function to setup for Heroku
setup_heroku() {
    print_header "Setting up for Heroku"
    
    if ! command -v heroku &> /dev/null; then
        print_error "Heroku CLI is not installed"
        print_status "Please install Heroku CLI: https://devcenter.heroku.com/articles/heroku-cli"
        return 1
    fi
    
    # Create Procfile
    echo "web: vendor/bin/heroku-php-apache2 public/" > Procfile
    
    # Update composer.json for Heroku
    if [ -f "composer.json" ]; then
        # Add Heroku-specific requirements
        print_status "Updating composer.json for Heroku..."
    fi
    
    print_status "Heroku setup files created"
    print_warning "To deploy to Heroku:"
    print_warning "1. heroku login"
    print_warning "2. heroku create your-app-name"
    print_warning "3. heroku addons:create cleardb:ignite"
    print_warning "4. heroku config:set APP_ENV=production"
    print_warning "5. git push heroku main"
}

# Main setup function
main() {
    # Check PHP requirements
    if ! check_php; then
        print_error "PHP requirements not met"
        exit 1
    fi
    
    # Create necessary directories
    print_status "Creating necessary directories..."
    mkdir -p logs uploads cache
    
    case $HOSTING_ENV in
        "vps")
            setup_vps
            ;;
        "shared")
            setup_shared
            ;;
        "heroku")
            setup_heroku
            ;;
        *)
            print_warning "Unknown hosting environment"
            print_status "Please choose setup type:"
            echo "1) VPS/Cloud hosting (DigitalOcean, AWS, etc.)"
            echo "2) Shared hosting (cPanel, Plesk)"
            echo "3) Heroku"
            read -p "Enter choice (1-3): " choice
            
            case $choice in
                1) setup_vps ;;
                2) setup_shared ;;
                3) setup_heroku ;;
                *) print_error "Invalid choice" ;;
            esac
            ;;
    esac
    
    print_status "Setup completed!"
    print_status "Check DEPLOYMENT.md for detailed deployment instructions"
}

# Run main function
main "$@"
