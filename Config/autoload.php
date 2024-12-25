<?php

spl_autoload_register(function ($class) {
    $path = __DIR__ . '/../App/';
    $file = $path . str_replace('\\', '/', $class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }else {
        error_log("Class file not found: " . $file);
    }
});
