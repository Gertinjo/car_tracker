<?php
// add_vehicle.php


require __DIR__ . '/config.php';

// Redirect if not logged in
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect + sanitize inputs
    $make       = trim($_POST['make']      ?? '');
    $model      = trim($_POST['model']     ?? '');
    $year       = (int)($_POST['year']     ?? 0);
    $mileage    = (int)($_POST['mileage']  ?? 0);
    $conditionn = $_POST['conditionn']     ?? '';
    $vin        = trim($_POST['vin']       ?? '');

    // Validate
    if ($make === '') {
        $errors[] = 'Brand is required.';
    }
    if ($model === '') {
        $errors[] = 'Model is required.';
    }
    if ($year < 1900 || $year > (int)date('Y')) {
        $errors[] = 'Enter a valid year.';
    }
    if ($mileage < 0) {
        $errors[] = 'Mileage must be 0 or more.';
    }
    if (!in_array($conditionn, ['New','Good','Fair','Poor'], true)) {
        $errors[] = 'Select a valid condition.';
    }

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmp      = $_FILES['photo']['tmp_name'];
        $ext      = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'veh_' . time() . '_' . rand(1000,9999) . '.' . $ext;
        $dest     = UPLOADS_DIR . $filename;

        if (!move_uploaded_file($tmp, $dest)) {
            $errors[] = 'Failed to upload photo.';
        }
    } else {
        $errors[] = 'Vehicle photo is required.';
    }

    // Insert when no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO vehicles 
              (user_id, make, model, year, mileage, conditionn, vin, photo)
            VALUES 
              (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $make,
            $model,
            $year,
            $mileage,
            $conditionn,
            $vin,
            $filename
        ]);

        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Vehicle | CarTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-6 text-center">Add New Vehicle</h2>

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
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1">Brand</label>
          <input
            name="make"
            value="<?= htmlspecialchars($make ?? '') ?>"
            required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Model</label>
          <input
            name="model"
            value="<?= htmlspecialchars($model ?? '') ?>"
            required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
          />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium mb-1">Year</label>
          <input
            type="number"
            name="year"
            value="<?= htmlspecialchars($year ?? '') ?>"
            min="1900"
            max="<?= date('Y') ?>"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Mileage (km)</label>
          <input
            type="number"
            name="mileage"
            value="<?= htmlspecialchars($mileage ?? '') ?>"
            min="0"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
          />
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Condition</label>
          <select
            name="conditionn"
            required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
          >
            <option value="">Select…</option>
            <?php foreach (['New','Good','Fair','Poor'] as $c): ?>
              <option
                value="<?= $c ?>"
                <?= (@$conditionn === $c ? 'selected' : '') ?>
              >
                <?= $c ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">VIN (optional)</label>
        <input
          name="vin"
          value="<?= htmlspecialchars($vin ?? '') ?>"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">Photo</label>
        <input
          type="file"
          name="photo"
          accept="image/*"
          required
          class="w-full px-2 py-1 border rounded-lg focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <button
        type="submit"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition"
      >
        Save Vehicle
      </button>
    </form>
  </div>
</body>
</html>
