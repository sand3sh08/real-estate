<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$stats = [
    'users'      => $pdo->query("SELECT COUNT(*) FROM users WHERE role != 'admin'")->fetchColumn(),
    'properties' => $pdo->query("SELECT COUNT(*) FROM properties")->fetchColumn(),
    'pending'    => $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'Pending'")->fetchColumn(),
    'approved'   => $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'Approved'")->fetchColumn(),
    'sellers'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'seller'")->fetchColumn(),
    'enquiries'  => $pdo->query("SELECT COUNT(*) FROM enquiries")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">Admin Dashboard</h2>
    <div class="row g-3 mb-4">
        <?php
        $cards = [
            ['Pending Properties', $stats['pending'], 'warning', 'properties.php?tab=pending'],
            ['Approved Properties', $stats['approved'], 'success', 'properties.php?tab=approved'],
            ['Total Users', $stats['users'], 'primary', 'users.php'],
            ['Sellers', $stats['sellers'], 'info', 'sellers.php'],
            ['Total Properties', $stats['properties'], 'secondary', 'properties.php'],
            ['Enquiries', $stats['enquiries'], 'dark', 'reports.php'],
        ];
        foreach ($cards as [$label, $count, $color, $link]):
        ?>
            <div class="col-md-4">
                <a href="<?= $link ?>" class="text-decoration-none">
                    <div class="card stat-card fade-in">
                        <div class="card-body">
                            <h6><?= $label ?></h6>
                            <h3><?= (int) $count ?></h3>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="d-flex gap-2">
        <a href="properties.php?tab=pending" class="btn btn-warning fade-in">Review Pending</a>
        <a href="users.php" class="btn btn-primary fade-in">Manage Users</a>
        <a href="reports.php" class="btn btn-outline-secondary fade-in">Reports</a>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
