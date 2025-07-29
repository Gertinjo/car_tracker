<?php
// vehicles.php



require __DIR__ . '/config.php';

// 1) Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2) Fetch all vehicles for this user
$stmt = $pdo->prepare('SELECT * FROM vehicles WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$vehicles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>My Vehicles | CarTracker</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
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
      <a href="vehicles.php" class="bg-gray-700"><i class="fas fa-car"></i> Vehicles</a>
      <a href="service_logs.php"><i class="fas fa-tools"></i> Service Logs</a>
      <a href="reminders.php"><i class="fas fa-bell"></i> Reminders</a>
      <a href="car_models.php"><i class="fas fa-car"></i> Car Models</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a>
    </nav>
  </aside>

  <!-- Main content -->
  <main class="main flex-1 p-6">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-semibold text-gray-800">My Vehicles</h1>
      <button
        onclick="location.href='add_vehicle.php'"
        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
      >
        <i class="fas fa-plus mr-1"></i> Add Vehicle
      </button>
    </div>

    <div class="card">
      <?php if ($vehicles): ?>
        <div class="overflow-x-auto">
          <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
              <tr class="bg-gray-200 text-left">
                <th class="px-4 py-2">Photo</th>
                <th class="px-4 py-2">Make & Model</th>
                <th class="px-4 py-2">Year</th>
                <th class="px-4 py-2">Mileage</th>
                <th class="px-4 py-2">Condition</th>
                <th class="px-4 py-2">VIN</th>
                <th class="px-4 py-2">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vehicles as $v): ?>
                <tr class="border-t">
                  <td class="px-4 py-2">
                    <div class="w-16 h-16 border-2 border-gray-300 rounded overflow-hidden">
                      <img
                        src="uploads/<?= htmlspecialchars($v['photo']) ?>"
                        alt="<?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?>"
                        class="w-full h-full object-cover"
                      >
                    </div>
                  </td>
                  <td class="px-4 py-2"><?= htmlspecialchars($v['make'] . ' ' . $v['model']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($v['year']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($v['mileage']) ?> km</td>
                  <td class="px-4 py-2"><?= htmlspecialchars($v['conditionn']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($v['vin']) ?></td>
                  <td class="px-4 py-2">
                    <a
                      href="edit_vehicle.php?id=<?= $v['id'] ?>"
                      class="text-blue-600 hover:underline mr-2"
                    >Edit</a>
                    <a
                      href="delete_vehicle.php?id=<?= $v['id'] ?>"
                      onclick="return confirm('Delete this vehicle?')"
                      class="text-red-600 hover:underline"
                    >Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="text-gray-700">You haven’t added any vehicles yet.</p>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
