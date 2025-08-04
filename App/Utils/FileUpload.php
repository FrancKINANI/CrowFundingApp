<?php

/**
 * File Upload Utility
 * 
 * Handles secure file uploads with validation and processing
 */
class FileUpload {
    
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    private $errors = [];
    
    public function __construct($uploadDir = 'uploads/', $allowedTypes = null, $maxFileSize = null) {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        $this->allowedTypes = $allowedTypes ?? ALLOWED_IMAGE_TYPES;
        $this->maxFileSize = $maxFileSize ?? MAX_UPLOAD_SIZE;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload a single file
     * 
     * @param array $file $_FILES array element
     * @param string $subDir Subdirectory within upload dir
     * @return array|false Upload result or false on failure
     */
    public function uploadFile($file, $subDir = '') {
        $this->errors = [];
        
        // Validate file
        $validation = SecurityMiddleware::validateFileUpload($file, $this->allowedTypes, $this->maxFileSize);
        if (!$validation['valid']) {
            $this->errors = $validation['errors'];
            return false;
        }
        
        // Create subdirectory if specified
        $targetDir = $this->uploadDir;
        if (!empty($subDir)) {
            $targetDir .= trim($subDir, '/') . '/';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
        }
        
        // Generate unique filename
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileName = $this->generateUniqueFileName($fileExtension);
        $targetPath = $targetDir . $fileName;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Process image if it's an image file
            $this->processImage($targetPath, $validation['mime_type']);
            
            return [
                'success' => true,
                'filename' => $fileName,
                'path' => $targetPath,
                'relative_path' => str_replace($this->uploadDir, '', $targetPath),
                'url' => $this->getFileUrl($targetPath),
                'size' => filesize($targetPath),
                'mime_type' => $validation['mime_type']
            ];
        } else {
            $this->errors[] = "Failed to move uploaded file";
            return false;
        }
    }
    
    /**
     * Upload multiple files
     * 
     * @param array $files $_FILES array for multiple files
     * @param string $subDir Subdirectory within upload dir
     * @return array Upload results
     */
    public function uploadMultipleFiles($files, $subDir = '') {
        $results = [];
        
        // Normalize files array structure
        $normalizedFiles = $this->normalizeFilesArray($files);
        
        foreach ($normalizedFiles as $file) {
            $result = $this->uploadFile($file, $subDir);
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Generate unique filename
     * 
     * @param string $extension
     * @return string
     */
    private function generateUniqueFileName($extension) {
        return uniqid('file_', true) . '.' . $extension;
    }
    
    /**
     * Process uploaded image (resize, optimize)
     * 
     * @param string $filePath
     * @param string $mimeType
     * @return bool
     */
    private function processImage($filePath, $mimeType) {
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
            return false;
        }
        
        try {
            // Get image dimensions
            list($width, $height) = getimagesize($filePath);
            
            // Resize if image is too large
            $maxWidth = 1920;
            $maxHeight = 1080;
            
            if ($width > $maxWidth || $height > $maxHeight) {
                $this->resizeImage($filePath, $maxWidth, $maxHeight, $mimeType);
            }
            
            // Create thumbnail
            $this->createThumbnail($filePath, $mimeType);
            
            return true;
        } catch (Exception $e) {
            error_log("Error processing image: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Resize image
     * 
     * @param string $filePath
     * @param int $maxWidth
     * @param int $maxHeight
     * @param string $mimeType
     * @return bool
     */
    private function resizeImage($filePath, $maxWidth, $maxHeight, $mimeType) {
        list($width, $height) = getimagesize($filePath);
        
        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
        
        // Create image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        if (!$source) return false;
        
        // Create new image
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // Preserve transparency for PNG and GIF
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $newWidth, $newHeight, $transparent);
        }
        
        // Resize image
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        // Save resized image
        switch ($mimeType) {
            case 'image/jpeg':
                imagejpeg($resized, $filePath, 85);
                break;
            case 'image/png':
                imagepng($resized, $filePath, 8);
                break;
            case 'image/gif':
                imagegif($resized, $filePath);
                break;
            case 'image/webp':
                imagewebp($resized, $filePath, 85);
                break;
        }
        
        // Clean up
        imagedestroy($source);
        imagedestroy($resized);
        
        return true;
    }
    
    /**
     * Create thumbnail
     * 
     * @param string $filePath
     * @param string $mimeType
     * @return bool
     */
    private function createThumbnail($filePath, $mimeType) {
        $thumbnailPath = $this->getThumbnailPath($filePath);
        $thumbnailDir = dirname($thumbnailPath);
        
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }
        
        list($width, $height) = getimagesize($filePath);
        
        // Thumbnail dimensions
        $thumbWidth = 300;
        $thumbHeight = 200;
        
        // Create image resource
        switch ($mimeType) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($filePath);
                break;
            default:
                return false;
        }
        
        if (!$source) return false;
        
        // Create thumbnail
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // Preserve transparency
        if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);
            $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
            imagefilledrectangle($thumbnail, 0, 0, $thumbWidth, $thumbHeight, $transparent);
        }
        
        // Resize to thumbnail
        imagecopyresampled($thumbnail, $source, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $width, $height);
        
        // Save thumbnail as JPEG
        imagejpeg($thumbnail, $thumbnailPath, 80);
        
        // Clean up
        imagedestroy($source);
        imagedestroy($thumbnail);
        
        return true;
    }
    
    /**
     * Get thumbnail path
     * 
     * @param string $filePath
     * @return string
     */
    private function getThumbnailPath($filePath) {
        $pathInfo = pathinfo($filePath);
        return $pathInfo['dirname'] . '/thumbs/' . $pathInfo['filename'] . '_thumb.jpg';
    }
    
    /**
     * Get file URL
     * 
     * @param string $filePath
     * @return string
     */
    private function getFileUrl($filePath) {
        $relativePath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $filePath);
        return app_url($relativePath);
    }
    
    /**
     * Normalize files array for multiple uploads
     * 
     * @param array $files
     * @return array
     */
    private function normalizeFilesArray($files) {
        $normalized = [];
        
        if (isset($files['name']) && is_array($files['name'])) {
            foreach ($files['name'] as $key => $name) {
                $normalized[] = [
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];
            }
        } else {
            $normalized[] = $files;
        }
        
        return $normalized;
    }
    
    /**
     * Delete file
     * 
     * @param string $filePath
     * @return bool
     */
    public function deleteFile($filePath) {
        if (file_exists($filePath)) {
            // Delete thumbnail if exists
            $thumbnailPath = $this->getThumbnailPath($filePath);
            if (file_exists($thumbnailPath)) {
                unlink($thumbnailPath);
            }
            
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Get upload errors
     * 
     * @return array
     */
    public function getErrors() {
        return $this->errors;
    }
}
