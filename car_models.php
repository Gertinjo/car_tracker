<?php
// car_models.php
require_once __DIR__ . '/config.php';
if (empty($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch all models + wishlist status
$stmt = $pdo->prepare("
  SELECT 
    cm.*,
    EXISTS(
      SELECT 1 FROM user_wishlist uw
      WHERE uw.user_id = ? AND uw.car_model_id = cm.id
    ) AS in_wishlist
  FROM car_models cm
  ORDER BY cm.make, cm.model
");
$stmt->execute([$_SESSION['user_id']]);
$models = $stmt->fetchAll();

$pageTitle = 'Car Models';
include __DIR__ . '/header.php';
?>

<!-- Page Title -->
<h1 class="text-2xl font-semibold mb-6">Car Models</h1>

<!-- Models List -->
<div class="space-y-6">
  <?php foreach ($models as $m): ?>
    <div class="flex items-center bg-white p-4 rounded-lg shadow">
      <div class="flex-1">
        <h3 class="font-semibold text-lg">
          <?= htmlspecialchars("{$m['make']} {$m['model']}") ?>
        </h3>
        <p class="text-gray-600 text-sm">
          Avg. Price: €<?= number_format($m['avg_price']) ?> &bull;
          0–100: <?= $m['zero_to_hundred'] ?>s &bull;
          <?= $m['fuel_consumption'] > 0 
              ? "{$m['fuel_consumption']} L/100 km" 
              : 'Electric' 
          ?>
        </p>
      </div>
      <form method="POST" action="toggle_wishlist.php">
        <input type="hidden" name="model_id" value="<?= $m['id'] ?>">
        <button
          name="toggle"
          class="px-4 py-2 rounded-lg text-white <?= $m['in_wishlist']
            ? 'bg-red-600 hover:bg-red-700' 
            : 'bg-blue-600 hover:bg-blue-700' ?>"
        >
          <?= $m['in_wishlist'] ? 'Remove' : 'Add' ?>
        </button>
      </form>
    </div>
  <?php endforeach; ?>
</div>

<!-- Centered View Wishlist Button at Bottom -->
<div class="text-center mt-8">
  <a
    href="wishlist.php"
    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition"
  >
    View Wishlist
  </a>
</div>

<?php include __DIR__ . '/footer.php'; ?>
