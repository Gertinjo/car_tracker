<?php
// config.php
// Start session on every page
session_start();

// Database connection settings
$host     = '127.0.0.1';
$db       = 'car_tracker';      // the database you created
$user     = 'root';             // default XAMPP MySQL user
$pass     = '';                 // default XAMPP MySQL password is empty
$charset  = 'utf8mb4';

// Data Source Name
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options for best practices
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // use real prepared statements
];

try {
    // Create the PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // If something goes wrong, stop and show error
    die("Database connection failed: " . $e->getMessage());
}

// (Optional) Define some handy constants
define('UPLOADS_DIR', __DIR__ . '/uploads/');
