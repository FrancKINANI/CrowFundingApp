<?php
/**
 * Autoloader configuration
 * Automatically loads class files for models, controllers, and core utilities.
 */

// Load classes dynamically based on their namespace or directory
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '../Models/',
        __DIR__ . '../Controllers/',
        __DIR__ . '../Views/'
    ];

    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            break;
        }
    }
});
