<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// If user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirectByRole($_SESSION['role']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "Invalid email or password.";
        } elseif (!password_verify($password, $user['password'])) {
            $error = "Invalid email or password.";
        } elseif ($user['status'] === 'blocked') {
            $error = "Your account has been blocked.";
        } elseif ($user['status'] === 'rejected') {
            $error = "Your account has been rejected.";
        } elseif ($user['status'] === 'pending' && $user['role'] === 'seller') {
            $error = "Your seller account is waiting for admin approval.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            redirectByRole($user['role']);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Real Estate</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- FIXED: Using BASE_URL constant -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- =====================================================
     LOGIN PAGE – ADVANCED
===================================================== -->

<section class="auth-page login-page">
    <!-- Decorative floating shapes -->
    <div class="auth-shape auth-shape-1"></div>
    <div class="auth-shape auth-shape-2"></div>
    <div class="auth-shape auth-shape-3"></div>
    <div class="auth-shape auth-shape-4"></div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card-modern fade-in">
                    <div class="auth-card-header">
                        <div class="auth-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <h2>Welcome Back</h2>
                        <p class="text-muted">Login to your account to continue</p>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-key"></i> Password
                            </label>
                            <div class="password-wrapper">
                                <input type="password" name="password" class="form-control" id="loginPassword" placeholder="Enter your password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('loginPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-auth">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Don't have an account? <a href="<?= BASE_URL ?>/register.php">Register</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>

<script>
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
</script>

</body>
</html>