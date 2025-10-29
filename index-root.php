<?php
/**
 * Root index.php - Redirects to public folder
 * For use in 000webhost and similar hosting services
 */

// Check if public folder exists
if (!is_dir('public')) {
    die('Error: public directory not found. Please ensure all files were uploaded correctly.');
}

// Check if public/index.php exists
if (!file_exists('public/index.php')) {
    die('Error: public/index.php not found. Please ensure all files were uploaded correctly.');
}

// Change working directory to public
chdir('public');

// Set the proper document root for includes
$_SERVER['DOCUMENT_ROOT'] = __DIR__ . '/public';

// Include the main application
require 'index.php';
