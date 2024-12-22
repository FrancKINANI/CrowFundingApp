<?php

spl_autoload_register(function ($class) {
    $baseDir = realpath(__DIR__ . '/../App/') . DIRECTORY_SEPARATOR;

    $directories = [
        'Models' . DIRECTORY_SEPARATOR,
        'Controllers' . DIRECTORY_SEPARATOR,
        'Views' . DIRECTORY_SEPARATOR
    ];

    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    foreach ($directories as $directory) {
        $file = $baseDir . $directory . $classPath;

        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }

    error_log("Autoload error: Unable to load class '$class' from any of the defined directories.");
});