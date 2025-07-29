<?php
// logout.php

require __DIR__ . '/config.php';

// Clear all session data
$_SESSION = [];
session_unset();
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
