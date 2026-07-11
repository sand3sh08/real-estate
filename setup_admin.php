<?php
// Run this once in the browser after importing database.sql to create
// your admin login, then DELETE this file.
require_once __DIR__ . '/includes/auth.php';

$done = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    if ($stmt->fetch()) {
        $error = 'An admin account already exists. Delete this file for security.';
    } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Fill in a valid name, email, and a password of at least 6 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?,?,?,?, 'admin', 'Active')");
        $stmt->execute([$name, $email, '', $hash]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>Create admin account</title>
<link rel="stylesheet" href="assets/css/style.css"></head>
<body>
<div class="container">
  <div class="form-card form-narrow">
    <h1 style="font-size:24px;">Create the admin account</h1>
    <?php if ($done): ?>
      <div class="alert alert-success">Admin account created. <strong>Delete setup_admin.php now</strong>, then <a href="login.php">log in</a>.</div>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert-error"><?= h($error) ?></div><?php endif; ?>
      <form method="POST">
        <div class="field"><label>Name</label><input type="text" name="name" required></div>
        <div class="field"><label>Email</label><input type="email" name="email" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" required minlength="6"></div>
        <button type="submit" class="btn btn-solid btn-block">Create admin</button>
      </form>
    <?php endif; ?>
  </div>
</div>
</body></html>
