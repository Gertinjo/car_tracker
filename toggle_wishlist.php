<?php
// toggle_wishlist.php
require_once __DIR__.'/config.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$modelId = (int)($_POST['model_id'] ?? 0);
if ($modelId < 1) {
  header('Location: car_models.php');
  exit;
}

// Check if already in wishlist
$chk = $pdo->prepare(
  'SELECT 1 FROM user_wishlist WHERE user_id = ? AND car_model_id = ?'
);
$chk->execute([$_SESSION['user_id'], $modelId]);

if ($chk->fetch()) {
  // remove
  $del = $pdo->prepare(
    'DELETE FROM user_wishlist WHERE user_id = ? AND car_model_id = ?'
  );
  $del->execute([$_SESSION['user_id'], $modelId]);
} else {
  // add
  $ins = $pdo->prepare(
    'INSERT INTO user_wishlist (user_id, car_model_id) VALUES (?, ?)'
  );
  $ins->execute([$_SESSION['user_id'], $modelId]);
}

header('Location: car_models.php');
exit;
