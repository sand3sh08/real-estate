<?php
$role = $_SESSION['role'] ?? null;
$name = $_SESSION['name'] ?? 'Guest';
$unreadCount = 0;

// Get unread message count for all logged-in users
if ($role) {
    require_once __DIR__ . '/db.php';
    if (file_exists(__DIR__ . '/messaging_functions.php')) {
        require_once __DIR__ . '/messaging_functions.php';
        $unreadCount = getUnreadCount($pdo, (int) $_SESSION['user_id']);
    }
}
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= BASE_URL ?>/index.php">
            RealEstate
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/search.php">Search</a></li>

                <?php if ($role === 'seller'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/seller/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/seller/my_properties.php">My Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/seller/add_property.php">Add Property</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/brokers.php">View Brokers</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/messaging/inbox.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a></li>
                <?php elseif ($role === 'buyer'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/buyer/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/buyer/favourites.php">Favourites</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/brokers.php">View Brokers</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/messaging/inbox.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a></li>
                <?php elseif ($role === 'admin'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/properties.php">Properties</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/users.php">Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/sellers.php">Sellers</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/admin/reports.php">Reports</a></li>
                <?php elseif ($role === 'broker'): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/broker/dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/broker/profile.php">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/messaging/inbox.php">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($unreadCount > 0): ?>
                            <span class="badge bg-danger ms-1"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <?php if ($role): ?>
                    <li class="nav-item"><span class="nav-link text-white-50">Hi, <?= htmlspecialchars($name) ?></span></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/logout.php">Logout</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/login.php">Login</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>/register.php">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>