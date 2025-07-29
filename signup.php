<?php


// signup.php

// Start session so we can store user ID after successful registration
// Include database connection (config.php sets up $pdo)
require 'config.php';

// Initialize an array to hold validation error messages
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1) Collect and sanitize form inputs
    $name    = trim($_POST['name']    ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $email   = trim($_POST['email']   ?? '');
    $pass    = $_POST['password']     ?? '';
    $phone   = trim($_POST['phone']   ?? '');
    $gender  = $_POST['gender']       ?? '';

    // 2) Simple validation checks
    if ($name === '') {
        $errors[] = 'Name is required.';
    }
    if ($surname === '') {
        $errors[] = 'Surname is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($pass) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($phone === '') {
        $errors[] = 'Phone number is required.';
    }
    if (!in_array($gender, ['male','female','other'], true)) {
        $errors[] = 'Please select your gender.';
    }

    // 3) If no validation errors, proceed to check uniqueness and insert
    if (empty($errors)) {
        // Prepare statement to see if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            // Email is taken
            $errors[] = 'That email is already registered.';
        } else {
            // 4) Hash the password using MD5 (per your request)
            $md5pass = md5($pass);

            // 5) Insert the new user into the database
            $insert = $pdo->prepare("
                INSERT INTO users 
                  (name, surname, email, password_hash, phone, gender) 
                VALUES 
                  (?, ?, ?, ?, ?, ?)
            ");
            $insert->execute([
                $name,
                $surname,
                $email,
                $md5pass,
                $phone,
                $gender
            ]);

            // 6) Log the user in by saving their user ID in session
            $_SESSION['user_id'] = $pdo->lastInsertId();

            // 7) Redirect to dashboard after successful signup
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Sign Up | CarTracker</title>
  <!-- Tailwind CSS CDN for quick, slick styling -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
  <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
    <!-- Page heading -->
    <h2 class="text-2xl font-bold mb-6 text-center">Create Your Account</h2>

    <!-- Display validation errors, if any -->
    <?php if (!empty($errors)): ?>
      <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        <ul class="list-disc list-inside">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <!-- Registration form -->
    <form method="POST" novalidate>
      <div class="grid grid-cols-2 gap-4">
        <!-- Name field -->
        <div>
          <label class="block text-sm font-medium mb-1">Name</label>
          <input
            name="name"
            value="<?= htmlspecialchars($name ?? '') ?>"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>

        <!-- Surname field -->
        <div>
          <label class="block text-sm font-medium mb-1">Surname</label>
          <input
            name="surname"
            value="<?= htmlspecialchars($surname ?? '') ?>"
            required
            class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
          />
        </div>
      </div>

      <!-- Email field -->
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

      <!-- Password field -->
      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">Password</label>
        <input
          type="password"
          name="password"
          required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <!-- Phone field -->
      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">Phone Number</label>
        <input
          name="phone"
          value="<?= htmlspecialchars($phone ?? '') ?>"
          required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
        />
      </div>

      <!-- Gender select -->
      <div class="mt-4">
        <label class="block text-sm font-medium mb-1">Gender</label>
        <select
          name="gender"
          required
          class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400"
        >
          <option value="">Select...</option>
          <option value="male"   <?= (@$gender === 'male'   ? 'selected' : '') ?>>Male</option>
          <option value="female" <?= (@$gender === 'female' ? 'selected' : '') ?>>Female</option>
          <option value="other"  <?= (@$gender === 'other'  ? 'selected' : '') ?>>Other</option>
        </select>
      </div>

      <!-- Submit button -->
      <button
        type="submit"
        class="mt-6 w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition"
      >
        Sign Up
      </button>
    </form>

    <!-- Link to login page -->
    <p class="mt-4 text-center text-sm text-gray-600">
      Already have an account?
      <a href="login.php" class="text-blue-600 hover:underline">Log in</a>
    </p>
  </div>
</body>
</html>
