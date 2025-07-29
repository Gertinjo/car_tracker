<?php
// header.php
require __DIR__ . '/config.php';

// allow pages to set $pageTitle before including this file
$pageTitle = $pageTitle ?? 'CarTracker';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Your overrides -->
  <link rel="stylesheet" href="css/style.css">
  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    crossorigin="anonymous"
  />
</head>
<body class="bg-gray-100 flex min-h-screen">
  <aside class="sidebar">
    <div class="logo">CarTracker</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="vehicles.php">Vehicles</a>
      <a href="service_logs.php">Service Logs</a>
      <a href="reminders.php">Reminders</a>
      <a href="logout.php">Log Out</a>
    </nav>
  </aside>

  <main class="main flex-1 p-6">
