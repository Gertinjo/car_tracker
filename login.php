<?php
// login.php\



// 1) Include config (which starts the session and gives us $pdo)
require 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 2) Grab & sanitize inputs
    $email = trim($_POST['email']    ?? '');
    $pass  = $_POST['password']      ?? '';

    // 3) Validate
    if ($email === '') {
        $errors[] = 'Email is required.';
    }
    if ($pass === '') {
        $errors[] = 'Password is required.';
    }

    // 4) If no errors, look up the user
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // 5) Compare MD5 of input to stored hash
        if ($user && md5($pass) === $user['password_hash']) {
            // 6) Success! Save user ID to session and redirect
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Log In | CarTracker</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-center">Log In to Your Account</h2>

    <!-- Error display -->
    <?php if (!empty($errors)): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Login form -->
    <form method="POST" novalidate>
      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">Email</label>
        <input
          type="email"
          name="email"
          value="<?= htmlspecialchars($email ?? '') ?>"
          required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">Password</label>
        <input
          type="password"
          name="password"
          required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <button
        type="submit"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition"
      >
        Log In
      </button>
    </form>

    <p class="mt-4 text-center text-sm text-gray-600">
      Don’t have an account?
      <a href="signup.php" class="text-blue-600 hover:underline">Sign up</a>
    </p>
  </div>
</body>
</html>           
