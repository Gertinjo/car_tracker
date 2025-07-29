<?php
// delete_vehicle.php


require __DIR__ . '/config.php';

// 1) Auth check
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}



// 2) Validate & fetch ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id < 1) {
    header('Location: vehicles.php');
    exit;
}

// 3) Make sure this vehicle belongs to the user
$stmt = $pdo->prepare('SELECT photo FROM vehicles WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$veh = $stmt->fetch();
if (! $veh) {
    header('Location: vehicles.php');
    exit;
}

// 4) Delete the photo file (optional)
$path = UPLOADS_DIR . $veh['photo'];
if (file_exists($path)) {
    @unlink($path);
}

// 5) Delete the DB record
$stmt = $pdo->prepare('DELETE FROM vehicles WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);

// 6) Redirect back
header('Location: vehicles.php');
exit;
