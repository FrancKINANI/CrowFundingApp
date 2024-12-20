<?php
    // Load classes dynamically based on their namespace or directory
    spl_autoload_register(function ($class) {
        $directories = [
            __DIR__ . '../App/Models/',
            __DIR__ . '../App/Controllers/',
            __DIR__ . '../App/Views/'
        ];

        foreach ($directories as $directory) {
            $file = $directory . $class . '.php';
            if (file_exists($file)) {
                require_once $file;
                break;
            }
        }
    });
