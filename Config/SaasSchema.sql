-- Enhanced SaaS Database Schema for CrowdFunding Platform
-- This schema includes all necessary tables for a production-ready SaaS application

-- Enhanced Users table with SaaS features
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    bio TEXT,
    avatar VARCHAR(255),
    location VARCHAR(255),
    website VARCHAR(255),
    phone VARCHAR(20),
    
    -- Email verification
    email_verified_at TIMESTAMP NULL,
    verification_token VARCHAR(255) NULL,
    
    -- Password reset
    reset_token VARCHAR(255) NULL,
    reset_token_expires_at TIMESTAMP NULL,
    
    -- Two-factor authentication
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255) NULL,
    two_factor_recovery_codes JSON NULL,
    
    -- Remember me functionality
    remember_token VARCHAR(255) NULL,
    remember_token_expires_at TIMESTAMP NULL,
    
    -- Account status and roles
    status ENUM('active', 'suspended', 'pending', 'deleted') DEFAULT 'pending',
    role ENUM('user', 'creator', 'admin', 'super_admin') DEFAULT 'user',
    is_verified BOOLEAN DEFAULT FALSE,
    is_admin BOOLEAN DEFAULT FALSE,
    
    -- Subscription and billing
    subscription_plan ENUM('free', 'pro', 'enterprise') DEFAULT 'free',
    stripe_customer_id VARCHAR(255) NULL,
    
    -- Activity tracking
    last_login_at TIMESTAMP NULL,
    last_activity_at TIMESTAMP NULL,
    login_count INT DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role (role),
    INDEX idx_subscription_plan (subscription_plan),
    INDEX idx_created_at (created_at)
);

-- Subscription plans
CREATE TABLE IF NOT EXISTS subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    billing_interval ENUM('month', 'year') DEFAULT 'month',
    
    -- Features
    project_limit INT DEFAULT 1, -- -1 for unlimited
    commission_rate DECIMAL(5, 4) DEFAULT 0.0500, -- 5%
    features JSON,
    
    -- Stripe integration
    stripe_price_id VARCHAR(255),
    stripe_product_id VARCHAR(255),
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User subscriptions
CREATE TABLE IF NOT EXISTS user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    
    -- Stripe data
    stripe_subscription_id VARCHAR(255),
    stripe_customer_id VARCHAR(255),
    
    -- Subscription details
    status ENUM('active', 'trialing', 'past_due', 'cancelled', 'unpaid', 'incomplete', 'incomplete_expired', 'paused', 'downgrade_scheduled') DEFAULT 'active',
    current_period_start TIMESTAMP,
    current_period_end TIMESTAMP,
    trial_ends_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,
    
    -- Scheduled changes
    scheduled_plan_id INT NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id),
    FOREIGN KEY (scheduled_plan_id) REFERENCES subscription_plans(id),
    
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_stripe_subscription_id (stripe_subscription_id)
);

-- API Keys for API access
CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    key_preview VARCHAR(20) NOT NULL, -- First few characters for display
    
    -- Permissions
    scopes JSON, -- ['read:projects', 'write:donations', etc.]
    
    -- Usage tracking
    usage_count INT DEFAULT 0,
    rate_limit_per_minute INT DEFAULT 60,
    last_used_at TIMESTAMP NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    expires_at TIMESTAMP NULL,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_key_hash (key_hash),
    INDEX idx_is_active (is_active)
);

-- Enhanced Projects table
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT NOT NULL,
    short_description VARCHAR(500),
    
    -- Financial details
    goal_amount DECIMAL(12, 2) NOT NULL,
    current_amount DECIMAL(12, 2) DEFAULT 0.00,
    currency VARCHAR(3) DEFAULT 'USD',
    min_donation DECIMAL(10, 2) DEFAULT 1.00,
    
    -- Project details
    user_id INT NOT NULL,
    category_id INT,
    featured_image VARCHAR(255),
    video_url VARCHAR(500),
    
    -- Timeline
    start_date DATE,
    end_date DATE,
    
    -- Status and moderation
    status ENUM('draft', 'pending_approval', 'active', 'funded', 'expired', 'cancelled', 'suspended') DEFAULT 'draft',
    approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    rejection_reason TEXT NULL,
    
    -- Features
    is_featured BOOLEAN DEFAULT FALSE,
    allow_anonymous_donations BOOLEAN DEFAULT TRUE,
    
    -- SEO
    meta_title VARCHAR(255),
    meta_description VARCHAR(500),
    
    -- Analytics
    view_count INT DEFAULT 0,
    share_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    
    INDEX idx_status (status),
    INDEX idx_category (category_id),
    INDEX idx_featured (is_featured),
    INDEX idx_end_date (end_date),
    INDEX idx_user_id (user_id),
    INDEX idx_slug (slug),
    FULLTEXT idx_search (title, description, short_description)
);

-- Enhanced Donations table with payment processing
CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(36) NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'USD',
    
    -- Relationships
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    
    -- Payment processing
    payment_method ENUM('stripe', 'paypal', 'bank_transfer') DEFAULT 'stripe',
    payment_status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending',
    
    -- Stripe integration
    stripe_payment_intent_id VARCHAR(255),
    stripe_charge_id VARCHAR(255),
    
    -- Transaction details
    transaction_id VARCHAR(255),
    gateway_fee DECIMAL(10, 2) DEFAULT 0.00,
    platform_fee DECIMAL(10, 2) DEFAULT 0.00,
    net_amount DECIMAL(10, 2),
    
    -- Donation details
    anonymous BOOLEAN DEFAULT FALSE,
    message TEXT,
    public_message BOOLEAN DEFAULT TRUE,
    
    -- Refund information
    refund_amount DECIMAL(10, 2) DEFAULT 0.00,
    refund_reason VARCHAR(255),
    refunded_at TIMESTAMP NULL,
    
    -- Failure information
    failure_reason VARCHAR(255),
    failure_code VARCHAR(50),
    
    -- Timestamps
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_project_id (project_id),
    INDEX idx_user_id (user_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_created_at (created_at),
    INDEX idx_stripe_payment_intent_id (stripe_payment_intent_id)
);

-- System settings
CREATE TABLE IF NOT EXISTS system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    key_name VARCHAR(255) NOT NULL UNIQUE,
    value TEXT,
    type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description TEXT,
    is_public BOOLEAN DEFAULT FALSE, -- Can be accessed via API
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_key_name (key_name),
    INDEX idx_is_public (is_public)
);

-- Activity logs for audit trail
CREATE TABLE IF NOT EXISTS activity_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    subject_type VARCHAR(255), -- 'project', 'user', 'donation', etc.
    subject_id INT NULL,
    
    -- Request details
    ip_address VARCHAR(45),
    user_agent TEXT,
    
    -- Additional data
    properties JSON,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_created_at (created_at)
);

-- Email templates
CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body_html TEXT NOT NULL,
    body_text TEXT,
    variables JSON, -- Available template variables
    
    is_active BOOLEAN DEFAULT TRUE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_name (name),
    INDEX idx_is_active (is_active)
);

-- Notifications
CREATE TABLE IF NOT EXISTS notifications (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(255) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    
    -- Notification data
    data JSON,
    
    -- Status
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    
    -- Channels
    sent_email BOOLEAN DEFAULT FALSE,
    sent_sms BOOLEAN DEFAULT FALSE,
    sent_push BOOLEAN DEFAULT FALSE,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_type (type),
    INDEX idx_created_at (created_at)
);

-- Insert default subscription plans
INSERT INTO subscription_plans (name, slug, description, price, project_limit, commission_rate, features, is_active, sort_order) VALUES
('Free', 'free', 'Perfect for getting started', 0.00, 1, 0.0500, '["1 project", "5% platform fee", "Basic support"]', TRUE, 1),
('Pro', 'pro', 'For serious creators', 29.99, 10, 0.0300, '["10 projects", "3% platform fee", "Priority support", "Advanced analytics"]', TRUE, 2),
('Enterprise', 'enterprise', 'For organizations', 99.99, -1, 0.0200, '["Unlimited projects", "2% platform fee", "Dedicated support", "Custom branding", "API access"]', TRUE, 3);

-- Insert default system settings
INSERT INTO system_settings (key_name, value, type, description, is_public) VALUES
('site_name', 'CrowdFund Pro', 'string', 'Site name', TRUE),
('site_description', 'Professional crowdfunding platform', 'string', 'Site description', TRUE),
('default_currency', 'USD', 'string', 'Default currency', TRUE),
('max_upload_size', '10485760', 'integer', 'Maximum file upload size in bytes', FALSE),
('maintenance_mode', 'false', 'boolean', 'Maintenance mode status', FALSE),
('registration_enabled', 'true', 'boolean', 'Allow new user registration', TRUE),
('email_verification_required', 'true', 'boolean', 'Require email verification', FALSE),
('min_project_goal', '100', 'integer', 'Minimum project goal amount', TRUE),
('max_project_goal', '1000000', 'integer', 'Maximum project goal amount', TRUE);

-- Insert default email templates
INSERT INTO email_templates (name, subject, body_html, variables) VALUES
('welcome', 'Welcome to {{site_name}}!', '<h1>Welcome {{user_name}}!</h1><p>Thank you for joining {{site_name}}.</p>', '["site_name", "user_name"]'),
('email_verification', 'Verify your email address', '<h1>Verify your email</h1><p>Click <a href="{{verification_url}}">here</a> to verify your email.</p>', '["verification_url", "user_name"]'),
('password_reset', 'Reset your password', '<h1>Reset Password</h1><p>Click <a href="{{reset_url}}">here</a> to reset your password.</p>', '["reset_url", "user_name"]'),
('donation_confirmation', 'Thank you for your donation!', '<h1>Donation Confirmed</h1><p>Thank you for donating ${{amount}} to {{project_title}}.</p>', '["amount", "project_title", "user_name"]'),
('project_approved', 'Your project has been approved!', '<h1>Project Approved</h1><p>Your project "{{project_title}}" has been approved and is now live.</p>', '["project_title", "user_name"]');
