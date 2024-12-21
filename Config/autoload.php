<?php
// Load classes dynamically based on their namespace or directory
spl_autoload_register(function ($class) {
    // Define the base directory for the application
    $baseDir = realpath(__DIR__ . '/../App/') . DIRECTORY_SEPARATOR;

    // Define the directories to search for class files
    $directories = [
        'Models' . DIRECTORY_SEPARATOR,
        'Controllers' . DIRECTORY_SEPARATOR,
        'Views' . DIRECTORY_SEPARATOR
    ];

    // Convert the class name to a file path
    $classPath = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';

    // Iterate through each directory to find the class file
    foreach ($directories as $directory) {
        $file = $baseDir . $directory . $classPath;

        if (file_exists($file)) {
            require_once $file;
            return; // Exit once the file is found and included
        }
    }

    // Log an error if the class file is not found
    error_log("Autoload error: Unable to load class '$class' from any of the defined directories.");
});