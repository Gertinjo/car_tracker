<?php
// config.php

// Only start session if none exists
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection settings
$host    = '127.0.0.1';
$db      = 'car_tracker';
$user    = 'root';
$pass    = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Only define this constant once
if (! defined('UPLOADS_DIR')) {
    define('UPLOADS_DIR', __DIR__ . '/uploads/');
}
