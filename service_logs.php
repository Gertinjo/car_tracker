<?php
// service_logs.php





require __DIR__ . '/config.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all vehicles for the dropdown
$stmt = $pdo->prepare('SELECT id, make, model FROM vehicles WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();

// Determine selected vehicle
$vehicle_id = isset($_GET['vehicle_id'])
    ? (int)$_GET['vehicle_id']
    : (count($vehicles) ? $vehicles[0]['id'] : 0);

// Fetch logs for that vehicle
$logs = [];
if ($vehicle_id) {
    $stmt = $pdo->prepare("
        SELECT *
        FROM service_logs
        WHERE vehicle_id = ?
        ORDER BY service_date DESC
    ");
    $stmt->execute([$vehicle_id]);
    $logs = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Service Logs | CarTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 flex min-h-screen">
  <!-- Sidebar with no icons -->
  <aside class="sidebar">
    <div class="logo">CarTracker</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="vehicles.php">Vehicles</a>
      <a href="service_logs.php" class="bg-gray-700">Service Logs</a>
      <a href="reminders.php">Reminders</a>
      <a href="car_models.php">Car Models</a>
      <a href="logout.php">Log Out</a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="main flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold">Service Logs</h1>
      <button
        onclick="location.href='add_service.php?vehicle_id=<?= $vehicle_id ?>'"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
      >Add Log</button>
    </div>

    <!-- Vehicle selector -->
    <form method="GET" class="mb-6">
      <label class="mr-2 font-medium">Select Vehicle:</label>
      <select
        name="vehicle_id"
        onchange="this.form.submit()"
        class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
      >
        <?php foreach ($vehicles as $v): ?>
          <option
            value="<?= $v['id'] ?>"
            <?= $v['id'] === $vehicle_id ? 'selected' : '' ?>
          >
            <?= htmlspecialchars("{$v['make']} {$v['model']}") ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>

    <!-- Logs table -->
    <div class="card">
      <?php if ($logs): ?>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
              <tr class="bg-gray-200 text-left">
                <th class="px-4 py-2">Date</th>
                <th class="px-4 py-2">Mileage</th>
                <th class="px-4 py-2">Type</th>
                <th class="px-4 py-2">Notes</th>
                <th class="px-4 py-2">Receipt</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($logs as $log): ?>
                <tr class="border-t">
                  <td class="px-4 py-2"><?= htmlspecialchars($log['service_date']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($log['mileage']) ?> km</td>
                  <td class="px-4 py-2"><?= htmlspecialchars($log['service_type']) ?></td>
                  <td class="px-4 py-2"><?= nl2br(htmlspecialchars($log['notes'])) ?></td>
                  <td class="px-4 py-2">
                    <?php if ($log['receipt_file']): ?>
                      <a
                        href="uploads/<?= htmlspecialchars($log['receipt_file']) ?>"
                        target="_blank"
                        class="text-blue-600 hover:underline"
                      >View</a>
                    <?php else: ?>
                      —
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-gray-700">No service logs for this vehicle yet.</p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
