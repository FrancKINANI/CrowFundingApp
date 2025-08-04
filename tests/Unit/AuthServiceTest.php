<?php

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

require_once __DIR__ . '/../../App/Services/AuthService.php';
require_once __DIR__ . '/../../App/Utils/EmailService.php';

class AuthServiceTest extends TestCase {
    private $authService;
    private $mockDb;
    private $mockEmailService;
    
    protected function setUp(): void {
        // Mock database
        $this->mockDb = $this->createMock(PDO::class);
        
        // Create AuthService instance
        $this->authService = new AuthService($this->mockDb);
        
        // Mock EmailService
        $this->mockEmailService = $this->createMock(EmailService::class);
        
        // Set up session for testing
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
    }
    
    protected function tearDown(): void {
        // Clean up session
        $_SESSION = [];
    }
    
    /**
     * Test successful user registration
     */
    public function testSuccessfulRegistration() {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'SecurePass123!'
        ];
        
        // Mock database interactions
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $this->mockDb->expects($this->once())
                     ->method('lastInsertId')
                     ->willReturn('123');
        
        $this->mockDb->expects($this->once())
                     ->method('beginTransaction');
        
        $this->mockDb->expects($this->once())
                     ->method('commit');
        
        // Mock email existence check
        $mockCheckStmt = $this->createMock(PDOStatement::class);
        $mockCheckStmt->expects($this->once())
                      ->method('execute')
                      ->willReturn(true);
        $mockCheckStmt->expects($this->once())
                      ->method('fetchColumn')
                      ->willReturn(0); // Email doesn't exist
        
        $this->mockDb->expects($this->at(1))
                     ->method('prepare')
                     ->willReturn($mockCheckStmt);
        
        $result = $this->authService->register($userData);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('123', $result['user_id']);
        $this->assertStringContainsString('verification', $result['message']);
    }
    
    /**
     * Test registration with invalid email
     */
    public function testRegistrationWithInvalidEmail() {
        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'SecurePass123!'
        ];
        
        $result = $this->authService->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertContains('Invalid email format', $result['errors']);
    }
    
    /**
     * Test registration with weak password
     */
    public function testRegistrationWithWeakPassword() {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'weak'
        ];
        
        $result = $this->authService->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertContains('Password must be at least 8 characters', $result['errors'][0]);
    }
    
    /**
     * Test registration with existing email
     */
    public function testRegistrationWithExistingEmail() {
        $userData = [
            'name' => 'John Doe',
            'email' => 'existing@example.com',
            'password' => 'SecurePass123!'
        ];
        
        // Mock email existence check
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('fetchColumn')
                 ->willReturn(1); // Email exists
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $result = $this->authService->register($userData);
        
        $this->assertFalse($result['success']);
        $this->assertContains('Email already registered', $result['errors']);
    }
    
    /**
     * Test successful login
     */
    public function testSuccessfulLogin() {
        $email = 'john@example.com';
        $password = 'SecurePass123!';
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => $email,
            'password' => $hashedPassword,
            'email_verified_at' => '2023-01-01 00:00:00',
            'status' => 'active',
            'two_factor_enabled' => false,
            'role' => 'user',
            'is_admin' => false,
            'subscription_plan' => 'free'
        ];
        
        // Mock rate limiting
        $_SESSION['rate_limits'] = [];
        
        // Mock database query
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->willReturn($userData);
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        // Mock update last login
        $mockUpdateStmt = $this->createMock(PDOStatement::class);
        $mockUpdateStmt->expects($this->once())
                       ->method('execute')
                       ->willReturn(true);
        
        $this->mockDb->expects($this->at(1))
                     ->method('prepare')
                     ->willReturn($mockUpdateStmt);
        
        $result = $this->authService->login($email, $password);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Login successful', $result['message']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($userData['id'], $_SESSION['user']['id']);
    }
    
    /**
     * Test login with invalid credentials
     */
    public function testLoginWithInvalidCredentials() {
        $email = 'john@example.com';
        $password = 'wrongpassword';
        
        // Mock rate limiting
        $_SESSION['rate_limits'] = [];
        
        // Mock database query returning no user
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->willReturn(false);
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $result = $this->authService->login($email, $password);
        
        $this->assertFalse($result['success']);
        $this->assertContains('Invalid email or password', $result['errors']);
    }
    
    /**
     * Test login with unverified email
     */
    public function testLoginWithUnverifiedEmail() {
        $email = 'john@example.com';
        $password = 'SecurePass123!';
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => $email,
            'password' => $hashedPassword,
            'email_verified_at' => null, // Not verified
            'status' => 'active',
            'two_factor_enabled' => false
        ];
        
        // Mock rate limiting
        $_SESSION['rate_limits'] = [];
        
        // Mock database query
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('fetch')
                 ->willReturn($userData);
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $result = $this->authService->login($email, $password);
        
        $this->assertFalse($result['success']);
        $this->assertContains('Please verify your email', $result['errors']);
    }
    
    /**
     * Test email verification
     */
    public function testEmailVerification() {
        $token = 'valid_verification_token';
        
        // Mock database update
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(1); // One row affected
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $result = $this->authService->verifyEmail($token);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Email verified successfully', $result['message']);
    }
    
    /**
     * Test email verification with invalid token
     */
    public function testEmailVerificationWithInvalidToken() {
        $token = 'invalid_token';
        
        // Mock database update
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        $mockStmt->expects($this->once())
                 ->method('rowCount')
                 ->willReturn(0); // No rows affected
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $result = $this->authService->verifyEmail($token);
        
        $this->assertFalse($result['success']);
        $this->assertContains('Invalid or expired verification token', $result['errors']);
    }
    
    /**
     * Test password reset request
     */
    public function testPasswordResetRequest() {
        $email = 'john@example.com';
        
        $userData = [
            'id' => 1,
            'name' => 'John Doe',
            'email' => $email
        ];
        
        // Mock user lookup
        $mockSelectStmt = $this->createMock(PDOStatement::class);
        $mockSelectStmt->expects($this->once())
                       ->method('execute')
                       ->willReturn(true);
        $mockSelectStmt->expects($this->once())
                       ->method('fetch')
                       ->willReturn($userData);
        
        // Mock token update
        $mockUpdateStmt = $this->createMock(PDOStatement::class);
        $mockUpdateStmt->expects($this->once())
                       ->method('execute')
                       ->willReturn(true);
        
        $this->mockDb->expects($this->at(0))
                     ->method('prepare')
                     ->willReturn($mockSelectStmt);
        
        $this->mockDb->expects($this->at(1))
                     ->method('prepare')
                     ->willReturn($mockUpdateStmt);
        
        $result = $this->authService->requestPasswordReset($email);
        
        $this->assertTrue($result['success']);
        $this->assertStringContainsString('reset link has been sent', $result['message']);
    }
    
    /**
     * Test password reset with valid token
     */
    public function testPasswordResetWithValidToken() {
        $token = 'valid_reset_token';
        $newPassword = 'NewSecurePass123!';
        
        $userData = ['id' => 1];
        
        // Mock token validation
        $mockSelectStmt = $this->createMock(PDOStatement::class);
        $mockSelectStmt->expects($this->once())
                       ->method('execute')
                       ->willReturn(true);
        $mockSelectStmt->expects($this->once())
                       ->method('fetch')
                       ->willReturn($userData);
        
        // Mock password update
        $mockUpdateStmt = $this->createMock(PDOStatement::class);
        $mockUpdateStmt->expects($this->once())
                       ->method('execute')
                       ->willReturn(true);
        
        $this->mockDb->expects($this->at(0))
                     ->method('prepare')
                     ->willReturn($mockSelectStmt);
        
        $this->mockDb->expects($this->at(1))
                     ->method('prepare')
                     ->willReturn($mockUpdateStmt);
        
        $result = $this->authService->resetPassword($token, $newPassword);
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Password reset successfully', $result['message']);
    }
    
    /**
     * Test logout
     */
    public function testLogout() {
        // Set up session
        $_SESSION['user'] = ['id' => 1, 'name' => 'John Doe'];
        
        // Mock clear remember me token
        $mockStmt = $this->createMock(PDOStatement::class);
        $mockStmt->expects($this->once())
                 ->method('execute')
                 ->willReturn(true);
        
        $this->mockDb->expects($this->once())
                     ->method('prepare')
                     ->willReturn($mockStmt);
        
        $result = $this->authService->logout();
        
        $this->assertTrue($result['success']);
        $this->assertEquals('Logged out successfully', $result['message']);
        $this->assertEmpty($_SESSION);
    }
    
    /**
     * Test rate limiting
     */
    public function testRateLimiting() {
        // Simulate multiple failed login attempts
        $_SESSION['rate_limits'] = [
            'login_127.0.0.1' => [
                'attempts' => 5,
                'last_attempt' => time()
            ]
        ];
        
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        
        $result = $this->authService->login('test@example.com', 'wrongpassword');
        
        $this->assertFalse($result['success']);
        $this->assertContains('Too many login attempts', $result['errors']);
    }
}
