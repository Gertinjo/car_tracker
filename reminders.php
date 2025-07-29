<?php
// reminders.php



require __DIR__ . '/config.php';

// 1) Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2) Handle toggle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vid     = (int)($_POST['vehicle_id'] ?? 0);
    $enabled = isset($_POST['reminder_enabled']) ? 1 : 0;

    // Only update if this vehicle belongs to the user
    $stmt = $pdo->prepare("
      UPDATE vehicles
      SET reminder_enabled = ?
      WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$enabled, $vid, $_SESSION['user_id']]);

    // Redirect to avoid resubmission
    header('Location: reminders.php');
    exit;
}

// 3) Fetch all user’s vehicles and their reminder setting
$stmt = $pdo->prepare("
  SELECT id, make, model, reminder_enabled
  FROM vehicles
  WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();

// 4) For each vehicle, compute next service date
$nextDates = [];
foreach ($vehicles as $v) {
    $stmt2 = $pdo->prepare("
      SELECT MIN(service_date) AS next_date
      FROM service_logs
      WHERE vehicle_id = ?
        AND service_date >= CURDATE()
    ");
    $stmt2->execute([$v['id']]);
    $row = $stmt2->fetch();
    $nextDates[$v['id']] = $row['next_date'] ?: null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Reminders | CarTracker</title>
  <!-- Tailwind CSS for styling -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-100 flex min-h-screen">
  <!-- Sidebar (no icons) -->
  <aside class="sidebar">
    <div class="logo">CarTracker</div>
    <nav>
      <a href="dashboard.php">Dashboard</a>
      <a href="vehicles.php">Vehicles</a>
      <a href="service_logs.php">Service Logs</a>
      <a href="reminders.php" class="bg-gray-700">Reminders</a>
      <a href="logout.php">Log Out</a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="main flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold">Reminders</h1>
    </div>

    <section class="card">
      <h2 class="text-xl font-semibold mb-4">Service Reminder Settings</h2>

      <?php if ($vehicles): ?>
        <div class="space-y-4">
          <?php foreach ($vehicles as $v): ?>
            <div class="flex items-center justify-between bg-white p-4 rounded-lg shadow">
              <div>
                <p class="font-semibold text-gray-800">
                  <?= htmlspecialchars("{$v['make']} {$v['model']}") ?>
                </p>
                <?php if ($nextDates[$v['id']]): ?>
                  <p class="text-gray-600 text-sm">
                    Next service on <?= htmlspecialchars($nextDates[$v['id']]) ?>
                  </p>
                <?php else: ?>
                  <p class="text-gray-600 text-sm">No upcoming service scheduled</p>
                <?php endif; ?>
              </div>
              <form method="POST" class="flex items-center">
                <input type="hidden" name="vehicle_id" value="<?= $v['id'] ?>">
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    name="reminder_enabled"
                    value="1"
                    <?= $v['reminder_enabled'] ? 'checked' : '' ?>
                    class="h-5 w-5 text-blue-600"
                  />
                  <span class="ml-2 text-gray-800">Email Reminder</span>
                </label>
                <button
                  type="submit"
                  class="ml-4 bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-lg"
                >
                  Save
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="text-gray-700">You haven’t added any vehicles yet.</p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
