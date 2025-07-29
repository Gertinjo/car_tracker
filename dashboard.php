<?php
// dashboard.php



require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user name
$stmt = $pdo->prepare('SELECT name FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Fetch vehicles
$stmt = $pdo->prepare('SELECT * FROM vehicles WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();

// Fetch reminders
$reminders = [];
foreach ($vehicles as $v) {
    $stmt2 = $pdo->prepare("
        SELECT MIN(service_date) AS next_date
        FROM service_logs
        WHERE vehicle_id = ?
          AND service_date >= CURDATE()
    ");
    $stmt2->execute([$v['id']]);
    $row = $stmt2->fetch();
    if ($row['next_date']) {
        $reminders[] = [
            'vehicle'   => $v,
            'next_date' => $row['next_date'],
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Dashboard | CarTracker</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Your custom styles -->
  <link rel="stylesheet" href="css/style.css">
  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
    integrity="sha512-pzwcG16yG3xE4WymN4j3hW3C4Z3OdsL+X0CpYZpjQv1eXGQi9N/F5hS+Xvj7g7YW+czsYgDwlR6xPR4Tf7Vy0g=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />
</head>
<body class="bg-gray-100 flex min-h-screen">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="logo">CarTracker</div>
    <nav>
      <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="vehicles.php"><i class="fas fa-car"></i> Vehicles</a>
      <a href="service_logs.php"><i class="fas fa-tools"></i> Service Logs</a>
      <a href="reminders.php"><i class="fas fa-bell"></i> Reminders</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="main flex-1 p-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
      <button
        onclick="location.href='add_vehicle.php'"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
      >
        <i class="fas fa-plus mr-1"></i> Add Vehicle
      </button>
    </div>

    <!-- Greeting -->
    <div class="card mb-6">
      <h2 class="text-xl font-bold mb-2">Welcome, <?= htmlspecialchars($user['name']) ?>!</h2>
      <p class="text-gray-700">Here’s a quick overview of your garage:</p>
    </div>

    <!-- Vehicles -->
    <div class="card mb-6">
      <h2 class="text-xl font-semibold mb-4">Your Vehicles</h2>

      <?php if ($vehicles): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <?php foreach ($vehicles as $v): ?>
            <div class="flex items-center bg-white p-4 rounded-lg shadow">
              <!-- Enlarged photo box: 96×96 px -->
              <div class="w-24 h-24 border-2 border-gray-300 rounded overflow-hidden flex-shrink-0 mr-4">
                <img
                  src="uploads/<?= htmlspecialchars($v['photo']) ?>"
                  alt="<?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?>"
                  class="w-full h-full object-cover"
                >
              </div>
              <div>
                <p class="font-semibold text-gray-800">
                  <?= htmlspecialchars("{$v['make']} {$v['model']} ({$v['year']})") ?>
                </p>
                <p class="text-gray-600 text-sm">Mileage: <?= htmlspecialchars($v['mileage']) ?> km</p>
                <p class="text-gray-600 text-sm">Condition: <?= htmlspecialchars($v['conditionn']) ?></p>
                <?php if (!empty($v['vin'])): ?>
                  <p class="text-gray-600 text-sm">VIN: <?= htmlspecialchars($v['vin']) ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-700">You haven’t added any vehicles yet.</p>
      <?php endif; ?>
    </div>

    <!-- Reminders -->
    <div class="card">
      <h2 class="text-xl font-semibold mb-4">Upcoming Service Reminders</h2>

      <?php if ($reminders): ?>
        <ul class="list-disc list-inside text-gray-700">
          <?php foreach ($reminders as $rem): ?>
            <li>
              <?= htmlspecialchars($rem['vehicle']['make'] . ' ' . $rem['vehicle']['model']) ?>
              — Next on <?= htmlspecialchars($rem['next_date']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-gray-700">No upcoming services scheduled.</p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
