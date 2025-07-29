<?php
// index.php



require __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Welcome | CarTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white p-8 rounded-xl shadow-lg text-center">
    <h1 class="text-3xl font-bold mb-6">Welcome to CarTracker</h1>

    <?php if (!empty($_SESSION['user_id'])): ?>
      <p class="mb-4 text-gray-700">You’re already logged in.</p>
      <a
        href="dashboard.php"
        class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
      >Go to Dashboard</a>
    <?php else: ?>
      <p class="mb-6 text-gray-700">Manage your vehicles and services all in one place.</p>
      <div class="space-x-4">
        <a
          href="login.php"
          class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-2 rounded-lg"
        >Log In</a>
        <a
          href="signup.php"
          class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg"
        >Sign Up</a>
      </div>
    <?php endif; ?>
  </div>
</body>
</html>
