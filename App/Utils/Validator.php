<?php

/**
 * Validation Utility Class
 * 
 * Provides comprehensive validation methods for user input
 */
class Validator {
    
    private $errors = [];
    private $data = [];
    
    public function __construct($data = []) {
        $this->data = $data;
    }
    
    /**
     * Validate required field
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function required($field, $message = null) {
        if (!isset($this->data[$field]) || empty(trim($this->data[$field]))) {
            $this->errors[$field][] = $message ?? "The {$field} field is required";
        }
        return $this;
    }
    
    /**
     * Validate email format
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function email($field, $message = null) {
        if (isset($this->data[$field]) && !empty($this->data[$field])) {
            if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
                $this->errors[$field][] = $message ?? "The {$field} must be a valid email address";
            }
        }
        return $this;
    }
    
    /**
     * Validate minimum length
     * 
     * @param string $field
     * @param int $length
     * @param string $message
     * @return self
     */
    public function min($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) < $length) {
            $this->errors[$field][] = $message ?? "The {$field} must be at least {$length} characters";
        }
        return $this;
    }
    
    /**
     * Validate maximum length
     * 
     * @param string $field
     * @param int $length
     * @param string $message
     * @return self
     */
    public function max($field, $length, $message = null) {
        if (isset($this->data[$field]) && strlen($this->data[$field]) > $length) {
            $this->errors[$field][] = $message ?? "The {$field} must not exceed {$length} characters";
        }
        return $this;
    }
    
    /**
     * Validate numeric value
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function numeric($field, $message = null) {
        if (isset($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field][] = $message ?? "The {$field} must be a number";
        }
        return $this;
    }
    
    /**
     * Validate positive number
     * 
     * @param string $field
     * @param string $message
     * @return self
     */
    public function positive($field, $message = null) {
        if (isset($this->data[$field]) && (float)$this->data[$field] <= 0) {
            $this->errors[$field][] = $message ?? "The {$field} must be a positive number";
        }
        return $this;
    }
    
    /**
     * Validate minimum value
     * 
     * @param string $field
     * @param float $min
     * @param string $message
     * @return self
     */
    public function minValue($field, $min, $message = null) {
        if (isset($this->data[$field]) && (float)$this->data[$field] < $min) {
            $this->errors[$field][] = $message ?? "The {$field} must be at least {$min}";
        }
        return $this;
    }
    
    /**
     * Validate maximum value
     * 
     * @param string $field
     * @param float $max
     * @param string $message
     * @return self
     */
    public function maxValue($field, $max, $message = null) {
        if (isset($this->data[$field]) && (float)$this->data[$field] > $max) {
            $this->errors[$field][] = $message ?? "The {$field} must not exceed {$max}";
        }
        return $this;
    }
    
    /**
     * Validate that field matches another field
     * 
     * @param string $field
     * @param string $matchField
     * @param string $message
     * @return self
     */
    public function matches($field, $matchField, $message = null) {
        if (isset($this->data[$field]) && isset($this->data[$matchField])) {
            if ($this->data[$field] !== $this->data[$matchField]) {
                $this->errors[$field][] = $message ?? "The {$field} must match {$matchField}";
            }
        }
        return $this;
    }
    
    /**
     * Validate using regex pattern
     * 
     * @param string $field
     * @param string $pattern
     * @param string $message
     * @return self
     */
    public function pattern($field, $pattern, $message = null) {
        if (isset($this->data[$field]) && !preg_match($pattern, $this->data[$field])) {
            $this->errors[$field][] = $message ?? "The {$field} format is invalid";
        }
        return $this;
    }
    
    /**
     * Validate that value is in array
     * 
     * @param string $field
     * @param array $values
     * @param string $message
     * @return self
     */
    public function in($field, $values, $message = null) {
        if (isset($this->data[$field]) && !in_array($this->data[$field], $values)) {
            $this->errors[$field][] = $message ?? "The {$field} must be one of: " . implode(', ', $values);
        }
        return $this;
    }
    
    /**
     * Custom validation callback
     * 
     * @param string $field
     * @param callable $callback
     * @param string $message
     * @return self
     */
    public function custom($field, $callback, $message = null) {
        if (isset($this->data[$field])) {
            $result = call_user_func($callback, $this->data[$field]);
            if (!$result) {
                $this->errors[$field][] = $message ?? "The {$field} is invalid";
            }
        }
        return $this;
    }
    
    /**
     * Check if validation passes
     * 
     * @return bool
     */
    public function passes() {
        return empty($this->errors);
    }
    
    /**
     * Check if validation fails
     * 
     * @return bool
     */
    public function fails() {
        return !$this->passes();
    }
    
    /**
     * Get all errors
     * 
     * @return array
     */
    public function errors() {
        return $this->errors;
    }
    
    /**
     * Get errors for specific field
     * 
     * @param string $field
     * @return array
     */
    public function getErrors($field) {
        return $this->errors[$field] ?? [];
    }
    
    /**
     * Get first error for field
     * 
     * @param string $field
     * @return string|null
     */
    public function getFirstError($field) {
        $errors = $this->getErrors($field);
        return !empty($errors) ? $errors[0] : null;
    }
    
    /**
     * Get all errors as flat array
     * 
     * @return array
     */
    public function getAllErrors() {
        $allErrors = [];
        foreach ($this->errors as $fieldErrors) {
            $allErrors = array_merge($allErrors, $fieldErrors);
        }
        return $allErrors;
    }
    
    /**
     * Static method to create validator instance
     * 
     * @param array $data
     * @return self
     */
    public static function make($data) {
        return new self($data);
    }
}
