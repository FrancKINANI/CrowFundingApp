<?php

require_once __DIR__ . '/../Utils/Logger.php';
require_once __DIR__ . '/../Utils/EmailService.php';
require_once __DIR__ . '/../Middleware/SecurityMiddleware.php';

class AuthService {
    private $db;
    private $emailService;
    
    public function __construct($db) {
        $this->db = $db;
        $this->emailService = new EmailService();
    }
    
    /**
     * Enhanced user registration with email verification
     */
    public function register($userData) {
        try {
            $this->db->beginTransaction();
            
            // Validate input
            $validation = $this->validateRegistrationData($userData);
            if (!$validation['valid']) {
                return ['success' => false, 'errors' => $validation['errors']];
            }
            
            // Check if email already exists
            if ($this->emailExists($userData['email'])) {
                return ['success' => false, 'errors' => ['Email already registered']];
            }
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            $hashedPassword = password_hash($userData['password'], PASSWORD_ARGON2ID);
            
            // Insert user
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, verification_token, email_verified_at, created_at) 
                VALUES (:name, :email, :password, :verification_token, NULL, NOW())
            ");
            
            $stmt->execute([
                'name' => SecurityMiddleware::sanitizeInput($userData['name']),
                'email' => filter_var($userData['email'], FILTER_SANITIZE_EMAIL),
                'password' => $hashedPassword,
                'verification_token' => $verificationToken
            ]);
            
            $userId = $this->db->lastInsertId();
            
            // Send verification email
            $this->sendVerificationEmail($userData['email'], $userData['name'], $verificationToken);
            
            // Log registration
            Logger::info('User registered', ['user_id' => $userId, 'email' => $userData['email']]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Registration successful. Please check your email to verify your account.',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            Logger::error('Registration failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
        }
    }
    
    /**
     * Enhanced login with rate limiting and 2FA support
     */
    public function login($email, $password, $rememberMe = false) {
        try {
            // Rate limiting check
            if (!SecurityMiddleware::checkRateLimit('login_' . $_SERVER['REMOTE_ADDR'], 5, 900)) {
                Logger::warning('Login rate limit exceeded', ['ip' => $_SERVER['REMOTE_ADDR']]);
                return ['success' => false, 'errors' => ['Too many login attempts. Please try again later.']];
            }
            
            // Get user
            $user = $this->getUserByEmail($email);
            if (!$user || !password_verify($password, $user['password'])) {
                Logger::warning('Failed login attempt', ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
                return ['success' => false, 'errors' => ['Invalid email or password']];
            }
            
            // Check if email is verified
            if (!$user['email_verified_at']) {
                return ['success' => false, 'errors' => ['Please verify your email before logging in']];
            }
            
            // Check if account is active
            if ($user['status'] !== 'active') {
                return ['success' => false, 'errors' => ['Account is suspended. Contact support.']];
            }
            
            // Check if 2FA is enabled
            if ($user['two_factor_enabled']) {
                // Store user ID in session for 2FA verification
                $_SESSION['2fa_user_id'] = $user['id'];
                $_SESSION['2fa_verified'] = false;
                return [
                    'success' => true,
                    'requires_2fa' => true,
                    'message' => 'Please enter your 2FA code'
                ];
            }
            
            // Complete login
            return $this->completeLogin($user, $rememberMe);
            
        } catch (Exception $e) {
            Logger::error('Login error', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Login failed. Please try again.']];
        }
    }
    
    /**
     * Verify 2FA code
     */
    public function verify2FA($code) {
        if (!isset($_SESSION['2fa_user_id'])) {
            return ['success' => false, 'errors' => ['Invalid session']];
        }
        
        $user = $this->getUserById($_SESSION['2fa_user_id']);
        if (!$user) {
            return ['success' => false, 'errors' => ['User not found']];
        }
        
        // Verify TOTP code
        if ($this->verifyTOTP($user['two_factor_secret'], $code)) {
            $_SESSION['2fa_verified'] = true;
            return $this->completeLogin($user);
        }
        
        return ['success' => false, 'errors' => ['Invalid 2FA code']];
    }
    
    /**
     * Complete login process
     */
    private function completeLogin($user, $rememberMe = false) {
        // Regenerate session ID
        session_regenerate_id(true);
        
        // Set session data
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'is_admin' => $user['is_admin'],
            'subscription_plan' => $user['subscription_plan']
        ];
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        
        // Handle remember me
        if ($rememberMe) {
            $this->setRememberMeToken($user['id']);
        }
        
        // Update last login
        $this->updateLastLogin($user['id']);
        
        // Log successful login
        Logger::logUserActivity('login', $user['id']);
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $_SESSION['user']
        ];
    }
    
    /**
     * Email verification
     */
    public function verifyEmail($token) {
        try {
            $stmt = $this->db->prepare("
                UPDATE users 
                SET email_verified_at = NOW(), verification_token = NULL 
                WHERE verification_token = :token AND email_verified_at IS NULL
            ");
            $stmt->execute(['token' => $token]);
            
            if ($stmt->rowCount() > 0) {
                Logger::info('Email verified', ['token' => substr($token, 0, 8) . '...']);
                return ['success' => true, 'message' => 'Email verified successfully'];
            }
            
            return ['success' => false, 'errors' => ['Invalid or expired verification token']];
            
        } catch (Exception $e) {
            Logger::error('Email verification failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Verification failed']];
        }
    }
    
    /**
     * Password reset request
     */
    public function requestPasswordReset($email) {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                // Don't reveal if email exists
                return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
            }
            
            $resetToken = bin2hex(random_bytes(32));
            $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET reset_token = :token, reset_token_expires_at = :expires_at 
                WHERE id = :user_id
            ");
            $stmt->execute([
                'token' => $resetToken,
                'expires_at' => $expiresAt,
                'user_id' => $user['id']
            ]);
            
            // Send reset email
            $this->sendPasswordResetEmail($user['email'], $user['name'], $resetToken);
            
            Logger::info('Password reset requested', ['user_id' => $user['id']]);
            
            return ['success' => true, 'message' => 'If the email exists, a reset link has been sent'];
            
        } catch (Exception $e) {
            Logger::error('Password reset request failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Reset request failed']];
        }
    }
    
    /**
     * Reset password with token
     */
    public function resetPassword($token, $newPassword) {
        try {
            $stmt = $this->db->prepare("
                SELECT id FROM users 
                WHERE reset_token = :token 
                AND reset_token_expires_at > NOW()
            ");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'errors' => ['Invalid or expired reset token']];
            }
            
            // Validate password
            if (!$this->validatePassword($newPassword)) {
                return ['success' => false, 'errors' => ['Password does not meet requirements']];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_ARGON2ID);
            
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = :password, reset_token = NULL, reset_token_expires_at = NULL 
                WHERE id = :user_id
            ");
            $stmt->execute([
                'password' => $hashedPassword,
                'user_id' => $user['id']
            ]);
            
            Logger::info('Password reset completed', ['user_id' => $user['id']]);
            
            return ['success' => true, 'message' => 'Password reset successfully'];
            
        } catch (Exception $e) {
            Logger::error('Password reset failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'errors' => ['Password reset failed']];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        // Clear remember me token if exists
        if (isset($_SESSION['user']['id'])) {
            $this->clearRememberMeToken($_SESSION['user']['id']);
            Logger::logUserActivity('logout', $_SESSION['user']['id']);
        }
        
        // Clear session
        session_unset();
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_me'])) {
            setcookie('remember_me', '', time() - 3600, '/');
        }
        
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    // Helper methods
    private function validateRegistrationData($data) {
        $errors = [];
        
        if (empty($data['name']) || strlen($data['name']) < 2) {
            $errors[] = 'Name must be at least 2 characters';
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (!$this->validatePassword($data['password'])) {
            $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, number, and special character';
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    private function validatePassword($password) {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password) &&
               preg_match('/[^A-Za-z0-9]/', $password);
    }
    
    private function emailExists($email) {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return $stmt->fetchColumn() > 0;
    }
    
    private function getUserByEmail($email) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getUserById($id) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function sendVerificationEmail($email, $name, $token) {
        $verificationUrl = APP_URL . "/verify-email?token=" . $token;
        $this->emailService->sendVerificationEmail($email, $name, $verificationUrl);
    }
    
    private function sendPasswordResetEmail($email, $name, $token) {
        $resetUrl = APP_URL . "/reset-password?token=" . $token;
        $this->emailService->sendPasswordResetEmail($email, $name, $resetUrl);
    }
    
    private function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $userId]);
    }
    
    private function setRememberMeToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $stmt = $this->db->prepare("
            UPDATE users 
            SET remember_token = :token, remember_token_expires_at = :expires_at 
            WHERE id = :user_id
        ");
        $stmt->execute([
            'token' => $token,
            'expires_at' => $expiresAt,
            'user_id' => $userId
        ]);
        
        setcookie('remember_me', $token, strtotime('+30 days'), '/', '', true, true);
    }
    
    private function clearRememberMeToken($userId) {
        $stmt = $this->db->prepare("
            UPDATE users 
            SET remember_token = NULL, remember_token_expires_at = NULL 
            WHERE id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
    }
    
    private function verifyTOTP($secret, $code) {
        // Implement TOTP verification logic
        // This would typically use a library like RobThree/TOTP
        return true; // Placeholder
    }
}
