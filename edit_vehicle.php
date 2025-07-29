<?php
// edit_vehicle.php


require __DIR__ . '/config.php';

if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    header('Location: vehicles.php');
    exit;
}

// 1) Fetch vehicle to edit
$stmt = $pdo->prepare('SELECT * FROM vehicles WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$veh = $stmt->fetch();
if (! $veh) {
    header('Location: vehicles.php');
    exit;
}

$errors = [];

// 2) Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make       = trim($_POST['make']      ?? '');
    $model      = trim($_POST['model']     ?? '');
    $year       = (int)($_POST['year']     ?? 0);
    $mileage    = (int)($_POST['mileage']  ?? 0);
    $conditionn = $_POST['conditionn']     ?? '';
    $vin        = trim($_POST['vin']       ?? '');
    $filename   = $veh['photo'];  // default

    // Validation
    if ($make === '')      $errors[] = 'Brand is required.';
    if ($model === '')     $errors[] = 'Model is required.';
    if ($year < 1900 || $year > (int)date('Y')) {
        $errors[] = 'Enter a valid year.';
    }
    if ($mileage < 0)      $errors[] = 'Mileage must be 0 or more.';
    if (!in_array($conditionn, ['New','Good','Fair','Poor'], true)) {
        $errors[] = 'Select a valid condition.';
    }

    // Photo upload (optional)
    if (!empty($_FILES['photo']['name'])) {
        if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $tmp  = $_FILES['photo']['tmp_name'];
            $ext  = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'veh_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dest = UPLOADS_DIR . $filename;
            if (!move_uploaded_file($tmp, $dest)) {
                $errors[] = 'Failed to upload new photo.';
            } else {
                // delete old file
                $old = UPLOADS_DIR . $veh['photo'];
                if (file_exists($old)) @unlink($old);
            }
        } else {
            $errors[] = 'Error uploading photo.';
        }
    }

    // Update DB if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE vehicles SET
              make       = ?,
              model      = ?,
              year       = ?,
              mileage    = ?,
              conditionn = ?,
              vin        = ?,
              photo      = ?
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([
            $make,
            $model,
            $year,
            $mileage,
            $conditionn,
            $vin,
            $filename,
            $id,
            $_SESSION['user_id']
        ]);
        header('Location: vehicles.php');
        exit;
    }
} else {
    // Pre-fill form vars
    $make       = $veh['make'];
    $model      = $veh['model'];
    $year       = $veh['year'];
    $mileage    = $veh['mileage'];
    $conditionn = $veh['conditionn'];
    $vin        = $veh['vin'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Vehicle | CarTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-lg">
    <h2 class="text-2xl font-bold mb-6 text-center">Edit Vehicle</h2>

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
          <input name="make" value="<?= htmlspecialchars($make) ?>" required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Model</label>
          <input name="model" value="<?= htmlspecialchars($model) ?>" required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
      </div>

      <div class="grid grid-cols-3 gap-4 mt-4">
        <div>
          <label class="block text-sm font-medium mb-1">Year</label>
          <input type="number" name="year" value="<?= htmlspecialchars($year) ?>" min="1900" max="<?= date('Y') ?>"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Mileage (km)</label>
          <input type="number" name="mileage" value="<?= htmlspecialchars($mileage) ?>" min="0"
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Condition</label>
          <select name="conditionn" required
            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
            <option value="">Select…</option>
            <?php foreach (['New','Good','Fair','Poor'] as $c): ?>
              <option value="<?= $c ?>" <?= ($conditionn === $c ? 'selected':'' ) ?>>
                <?= $c ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">VIN (optional)</label>
        <input name="vin" value="<?= htmlspecialchars($vin) ?>"
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-400">
      </div>

      <div class="mt-4 flex items-center">
        <div class="w-16 h-16 border-2 border-gray-300 rounded overflow-hidden mr-4">
          <img src="uploads/<?= htmlspecialchars($veh['photo']) ?>"
               class="w-full h-full object-cover">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Change Photo?</label>
          <input type="file" name="photo" accept="image/*"
            class="px-2 py-1 border rounded-lg focus:ring-2 focus:ring-blue-400">
        </div>
      </div>

      <button type="submit"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition">
        Update Vehicle
      </button>
    </form>
  </div>
</body>
</html>
