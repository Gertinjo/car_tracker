<?php
// add_service.php


require __DIR__ . '/config.php';

// 1) Auth
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// 2) Get & validate vehicle_id
$vehicle_id = isset($_GET['vehicle_id']) ? (int)$_GET['vehicle_id'] : 0;
if ($vehicle_id < 1) {
    header('Location: vehicles.php');
    exit;
}


// 3) Check ownership
$stmt = $pdo->prepare('SELECT make,model FROM vehicles WHERE id = ? AND user_id = ?');
$stmt->execute([$vehicle_id, $_SESSION['user_id']]);
$veh = $stmt->fetch();
if (!$veh) {
    header('Location: vehicles.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 4) Collect inputs
    $service_date = $_POST['service_date'] ?? '';
    $mileage      = (int)($_POST['mileage'] ?? 0);
    $type         = trim($_POST['service_type'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    // 5) Validate
    if (!$service_date)    $errors[] = 'Service date is required.';
    if ($mileage < 0)      $errors[] = 'Mileage must be 0 or more.';
    if ($type === '')      $errors[] = 'Service type is required.';

    // 6) Handle receipt upload (optional)
    $receipt = null;
    if (!empty($_FILES['receipt']['name'])) {
        if ($_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $ext  = pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION);
            $fn   = 'rec_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dst  = UPLOADS_DIR . $fn;
            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $dst)) {
                $receipt = $fn;
            } else {
                $errors[] = 'Failed to upload receipt file.';
            }
        } else {
            $errors[] = 'Error uploading receipt file.';
        }
    }

    // 7) Insert
    if (empty($errors)) {
        $ins = $pdo->prepare("
            INSERT INTO service_logs
              (vehicle_id, service_date, mileage, service_type, notes, receipt_file)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $ins->execute([
            $vehicle_id,
            $service_date,
            $mileage,
            $type,
            $notes ?: null,
            $receipt
        ]);
        header("Location: service_logs.php?vehicle_id={$vehicle_id}");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Service | CarTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-xl shadow-xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">
      Add Service for <?= htmlspecialchars($veh['make'].' '.$veh['model']) ?>
    </h2>

    <?php if ($errors): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" novalidate>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium mb-1">Service Date</label>
          <input
            type="date" name="service_date" value="<?= htmlspecialchars($service_date ?? '') ?>"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
            required
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Mileage (km)</label>
          <input
            type="number" name="mileage" min="0"
            value="<?= htmlspecialchars($mileage ?? '') ?>"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
            required
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Service Type</label>
          <input
            type="text" name="service_type"
            value="<?= htmlspecialchars($type ?? '') ?>"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
            required
          >
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Notes</label>
          <textarea
            name="notes" rows="3"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
          ><?= htmlspecialchars($notes ?? '') ?></textarea>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Receipt (optional)</label>
          <input
            type="file" name="receipt" accept="application/pdf,image/*"
            class="w-full px-2 py-1 border rounded-lg focus:ring-2 focus:ring-blue-400"
          >
        </div>
      </div>

      <button
        type="submit"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700"
      >
        Save Service
      </button>
    </form>
  </div>
</body>
</html>
