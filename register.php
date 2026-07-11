<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

// If user is already logged in
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    redirectByRole($_SESSION['role']);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $role     = $_POST['role'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm) || empty($role)) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif (!in_array($role, ['buyer', 'seller', 'broker'])) {
        $error = "Invalid role selected.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = "Email already exists.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $status = ($role === 'seller') ? 'pending' : 'active';
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$name, $email, $phone, $passwordHash, $role, $status]);
            $userId = $pdo->lastInsertId();

            if ($role === 'broker') {
                $stmt = $pdo->prepare("INSERT INTO brokers(user_id) VALUES(?)");
                $stmt->execute([$userId]);
            }

            if ($role == 'seller') {
                $success = "Registration successful. Your account is waiting for Admin approval.";
            } else {
                header('Location: ' . BASE_URL . '/login.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Real Estate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>

<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<!-- =====================================================
     REGISTER PAGE – ADVANCED
===================================================== -->

<section class="auth-page register-page">
    <!-- Decorative floating shapes -->
    <div class="auth-shape auth-shape-1"></div>
    <div class="auth-shape auth-shape-2"></div>
    <div class="auth-shape auth-shape-3"></div>
    <div class="auth-shape auth-shape-4"></div>

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-7 col-lg-6">
                <div class="auth-card-modern fade-in">
                    <div class="auth-card-header">
                        <div class="auth-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h2>Create Account</h2>
                        <p class="text-muted">Join India's smart property platform</p>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" class="auth-form">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user"></i> Full Name
                            </label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" placeholder="Enter your full name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Enter your email" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-phone"></i> Phone Number
                            </label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="Enter your phone number">
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-tag"></i> Register As
                            </label>
                            <select name="role" class="form-select" required>
                                <option value="">Select Role</option>
                                <option value="buyer">🏠 Buyer</option>
                                <option value="seller">🏢 Seller</option>
                                <option value="broker">🤝 Broker</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-key"></i> Password
                            </label>
                            <div class="password-wrapper">
                                <input type="password" name="password" class="form-control" id="regPassword" placeholder="Create a password (min 6 chars)" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('regPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-check-circle"></i> Confirm Password
                            </label>
                            <div class="password-wrapper">
                                <input type="password" name="confirm_password" class="form-control" id="regConfirmPassword" placeholder="Confirm your password" required>
                                <button type="button" class="toggle-password" onclick="togglePassword('regConfirmPassword', this)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-auth">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </form>

                    <div class="auth-footer">
                        <p>Already have an account? <a href="<?= BASE_URL ?>/login.php">Login</a></p>
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