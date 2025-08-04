<?php

/**
 * Simple Logger Class
 * 
 * Provides logging functionality for the application
 */
class Logger {
    
    const EMERGENCY = 'emergency';
    const ALERT = 'alert';
    const CRITICAL = 'critical';
    const ERROR = 'error';
    const WARNING = 'warning';
    const NOTICE = 'notice';
    const INFO = 'info';
    const DEBUG = 'debug';
    
    private static $logLevels = [
        self::EMERGENCY => 0,
        self::ALERT => 1,
        self::CRITICAL => 2,
        self::ERROR => 3,
        self::WARNING => 4,
        self::NOTICE => 5,
        self::INFO => 6,
        self::DEBUG => 7
    ];
    
    /**
     * Log a message
     * 
     * @param string $level
     * @param string $message
     * @param array $context
     */
    public static function log($level, $message, $context = []) {
        $configLevel = LOG_LEVEL;
        
        // Check if we should log this level
        if (self::$logLevels[$level] > self::$logLevels[$configLevel]) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $contextString = !empty($context) ? ' ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] {$level}: {$message}{$contextString}" . PHP_EOL;
        
        // Ensure log directory exists
        $logFile = LOG_FILE;
        $logDir = dirname($logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        // Write to log file
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        
        // Also log to error_log in development
        if (APP_ENV === 'development') {
            error_log($logMessage);
        }
    }
    
    /**
     * Log emergency message
     * 
     * @param string $message
     * @param array $context
     */
    public static function emergency($message, $context = []) {
        self::log(self::EMERGENCY, $message, $context);
    }
    
    /**
     * Log alert message
     * 
     * @param string $message
     * @param array $context
     */
    public static function alert($message, $context = []) {
        self::log(self::ALERT, $message, $context);
    }
    
    /**
     * Log critical message
     * 
     * @param string $message
     * @param array $context
     */
    public static function critical($message, $context = []) {
        self::log(self::CRITICAL, $message, $context);
    }
    
    /**
     * Log error message
     * 
     * @param string $message
     * @param array $context
     */
    public static function error($message, $context = []) {
        self::log(self::ERROR, $message, $context);
    }
    
    /**
     * Log warning message
     * 
     * @param string $message
     * @param array $context
     */
    public static function warning($message, $context = []) {
        self::log(self::WARNING, $message, $context);
    }
    
    /**
     * Log notice message
     * 
     * @param string $message
     * @param array $context
     */
    public static function notice($message, $context = []) {
        self::log(self::NOTICE, $message, $context);
    }
    
    /**
     * Log info message
     * 
     * @param string $message
     * @param array $context
     */
    public static function info($message, $context = []) {
        self::log(self::INFO, $message, $context);
    }
    
    /**
     * Log debug message
     * 
     * @param string $message
     * @param array $context
     */
    public static function debug($message, $context = []) {
        self::log(self::DEBUG, $message, $context);
    }
    
    /**
     * Log user activity
     * 
     * @param string $action
     * @param int $userId
     * @param array $data
     */
    public static function logUserActivity($action, $userId = null, $data = []) {
        $context = [
            'user_id' => $userId,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];
        
        self::info("User activity: {$action}", $context);
    }
    
    /**
     * Log database query (for debugging)
     * 
     * @param string $query
     * @param array $params
     * @param float $executionTime
     */
    public static function logQuery($query, $params = [], $executionTime = null) {
        if (APP_ENV !== 'development') {
            return;
        }
        
        $context = [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime
        ];
        
        self::debug("Database query executed", $context);
    }
}
