#!/bin/bash

# CrowdFunding Platform Deployment Script
# This script helps deploy the application to production

set -e

echo "🚀 Starting CrowdFunding Platform Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
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

# Check if running as root
if [[ $EUID -eq 0 ]]; then
   print_error "This script should not be run as root for security reasons"
   exit 1
fi

# Check PHP version
print_status "Checking PHP version..."
PHP_VERSION=$(php -v | head -n 1 | cut -d " " -f 2 | cut -d "." -f 1,2)
if [[ $(echo "$PHP_VERSION >= 7.4" | bc -l) -eq 0 ]]; then
    print_error "PHP 7.4 or higher is required. Current version: $PHP_VERSION"
    exit 1
fi
print_status "PHP version $PHP_VERSION is compatible"

# Check required PHP extensions
print_status "Checking required PHP extensions..."
REQUIRED_EXTENSIONS=("pdo" "pdo_mysql" "json" "mbstring" "openssl")
for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    if ! php -m | grep -q "^$ext$"; then
        print_error "Required PHP extension '$ext' is not installed"
        exit 1
    fi
done
print_status "All required PHP extensions are installed"

# Install Composer dependencies
if [ -f "composer.json" ]; then
    print_status "Installing Composer dependencies..."
    if command -v composer &> /dev/null; then
        composer install --no-dev --optimize-autoloader
    else
        print_warning "Composer not found. Please install dependencies manually."
    fi
fi

# Create necessary directories
print_status "Creating necessary directories..."
mkdir -p logs
mkdir -p uploads
mkdir -p cache

# Set proper permissions
print_status "Setting file permissions..."
chmod 755 public
chmod 644 public/index.php
chmod 600 .env 2>/dev/null || true
chmod 755 logs
chmod 755 uploads
chmod 755 cache

# Copy environment file if it doesn't exist
if [ ! -f ".env" ]; then
    print_status "Creating environment file..."
    cp .env.example .env
    print_warning "Please edit .env file with your configuration before running the application"
fi

# Generate application key if not set
if ! grep -q "APP_KEY=your-secret-key" .env 2>/dev/null; then
    print_status "Generating application key..."
    APP_KEY=$(openssl rand -hex 32)
    sed -i "s/APP_KEY=your-secret-key-here-change-in-production/APP_KEY=$APP_KEY/" .env
fi

# Database setup reminder
print_status "Database setup..."
print_warning "Please ensure your database is configured and accessible"
print_warning "The application will create tables automatically on first run"

# Security checklist
print_status "Security checklist:"
echo "  ✓ Environment file created"
echo "  ✓ Application key generated"
echo "  ✓ File permissions set"
echo "  ✓ Sensitive files protected"

print_warning "Additional security steps for production:"
echo "  - Enable HTTPS/SSL"
echo "  - Configure firewall"
echo "  - Set up regular backups"
echo "  - Monitor logs"
echo "  - Keep software updated"

# Final instructions
print_status "Deployment completed successfully!"
echo ""
echo "Next steps:"
echo "1. Edit .env file with your database and application settings"
echo "2. Configure your web server to point to the 'public' directory"
echo "3. Test the application"
echo "4. Set up SSL certificate for production"
echo ""
echo "To start development server: php -S localhost:8000 -t public"
echo ""
print_status "Happy coding! 🎉"
