<?php


// wishlist.php
require_once __DIR__.'/config.php';
if (empty($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

// Fetch user’s wishlist
$stmt = $pdo->prepare("
  SELECT cm.*
  FROM car_models cm
  JOIN user_wishlist uw ON uw.car_model_id = cm.id
  WHERE uw.user_id = ?
  ORDER BY cm.make, cm.model
");
$stmt->execute([$_SESSION['user_id']]);
$list = $stmt->fetchAll();
$pageTitle = 'My Wishlist';
include __DIR__.'/header.php';
?>
<?php if ($list): ?>
  <table class="min-w-full bg-white rounded-lg shadow overflow-x-auto mb-6">
    <thead>
      <tr class="bg-gray-200 text-left">
        <th class="px-4 py-2">Model</th>
        <th class="px-4 py-2">Avg. Price</th>
        <th class="px-4 py-2">0–100 km/h</th>
        <th class="px-4 py-2">Fuel</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($list as $m): ?>
        <tr class="border-t">
          <td class="px-4 py-2"><?=htmlspecialchars("{$m['make']} {$m['model']}")?></td>
          <td class="px-4 py-2">€<?=number_format($m['avg_price'])?></td>
          <td class="px-4 py-2"><?=$m['zero_to_hundred']?>s</td>
          <td class="px-4 py-2">
            <?= $m['fuel_consumption'] > 0
              ? "{$m['fuel_consumption']} L/100 km"
              : 'Electric' ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php else: ?>
  <p class="text-gray-700">Your wishlist is empty. Go add some cars!</p>
<?php endif; ?>
<?php include __DIR__.'/footer.php'; ?>
