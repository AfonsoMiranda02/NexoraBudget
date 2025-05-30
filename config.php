<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'nexorahub');

// Application configuration
define('BASE_URL', 'http://localhost/NexoraHubCusor');

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Path configurations
define('PUBLIC_PATH', __DIR__ . '/public');
define('IMAGES_PATH', PUBLIC_PATH . '/imgs');
define('CSS_PATH', PUBLIC_PATH . '/css');
define('JS_PATH', PUBLIC_PATH . '/js');

// Session configuration
session_start(); 