<?php

/**
 * Security Middleware
 * 
 * Handles security-related functionality including CSRF protection,
 * input sanitization, and security headers.
 */
class SecurityMiddleware {
    
    /**
     * Apply security headers
     */
    public static function applySecurityHeaders() {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' https://cdn.jsdelivr.net; " .
               "connect-src 'self';";
        header("Content-Security-Policy: $csp");
        
        // HTTPS enforcement in production
        if (APP_ENV === 'production' && !isset($_SERVER['HTTPS'])) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
    
    /**
     * Validate CSRF token
     * 
     * @param array $data
     * @return bool
     */
    public static function validateCSRF($data) {
        if (!isset($data['csrf_token'])) {
            return false;
        }
        
        return verify_csrf_token($data['csrf_token']);
    }
    
    /**
     * Sanitize input data
     * 
     * @param mixed $data
     * @return mixed
     */
    public static function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitizeInput'], $data);
        }
        
        if (is_string($data)) {
            // Remove null bytes
            $data = str_replace("\0", '', $data);
            
            // Trim whitespace
            $data = trim($data);
            
            // Convert special characters to HTML entities
            $data = htmlspecialchars($data, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        
        return $data;
    }
    
    /**
     * Validate email format
     * 
     * @param string $email
     * @return bool
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate password strength
     * 
     * @param string $password
     * @return array
     */
    public static function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < 8) {
            $errors[] = "Password must be at least 8 characters long";
        }
        
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Rate limiting check
     * 
     * @param string $key
     * @param int $maxAttempts
     * @param int $timeWindow
     * @return bool
     */
    public static function checkRateLimit($key, $maxAttempts = 5, $timeWindow = 300) {
        if (!isset($_SESSION['rate_limits'])) {
            $_SESSION['rate_limits'] = [];
        }
        
        $now = time();
        $rateLimitKey = $key . '_' . $_SERVER['REMOTE_ADDR'];
        
        if (!isset($_SESSION['rate_limits'][$rateLimitKey])) {
            $_SESSION['rate_limits'][$rateLimitKey] = [
                'attempts' => 1,
                'first_attempt' => $now
            ];
            return true;
        }
        
        $rateLimit = $_SESSION['rate_limits'][$rateLimitKey];
        
        // Reset if time window has passed
        if (($now - $rateLimit['first_attempt']) > $timeWindow) {
            $_SESSION['rate_limits'][$rateLimitKey] = [
                'attempts' => 1,
                'first_attempt' => $now
            ];
            return true;
        }
        
        // Check if max attempts exceeded
        if ($rateLimit['attempts'] >= $maxAttempts) {
            return false;
        }
        
        // Increment attempts
        $_SESSION['rate_limits'][$rateLimitKey]['attempts']++;
        return true;
    }
    
    /**
     * Log security event
     * 
     * @param string $event
     * @param array $data
     */
    public static function logSecurityEvent($event, $data = []) {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];
        
        error_log("SECURITY: " . json_encode($logData));
    }
    
    /**
     * Generate secure random token
     * 
     * @param int $length
     * @return string
     */
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Validate file upload
     * 
     * @param array $file
     * @param array $allowedTypes
     * @param int $maxSize
     * @return array
     */
    public static function validateFileUpload($file, $allowedTypes = [], $maxSize = null) {
        $errors = [];
        
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $errors[] = "No file uploaded or invalid file";
            return ['valid' => false, 'errors' => $errors];
        }
        
        // Check file size
        $maxSize = $maxSize ?? MAX_UPLOAD_SIZE;
        if ($file['size'] > $maxSize) {
            $errors[] = "File size exceeds maximum allowed size";
        }
        
        // Check file type
        if (!empty($allowedTypes)) {
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExtension, $allowedTypes)) {
                $errors[] = "File type not allowed";
            }
        }
        
        // Check for malicious content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp'
        ];
        
        if (!empty($allowedTypes) && !in_array($mimeType, $allowedMimes)) {
            $errors[] = "Invalid file content";
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $mimeType
        ];
    }
}
