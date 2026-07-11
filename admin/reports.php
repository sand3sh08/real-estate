<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$byType = $pdo->query("
    SELECT property_type, COUNT(*) AS cnt, AVG(price) AS avg_price
    FROM properties WHERE status = 'Approved'
    GROUP BY property_type
")->fetchAll();

$byCity = $pdo->query("
    SELECT city, COUNT(*) AS cnt
    FROM properties WHERE status = 'Approved'
    GROUP BY city ORDER BY cnt DESC LIMIT 10
")->fetchAll();

$monthlyStats = $pdo->query("
    SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS cnt
    FROM properties WHERE status = 'Approved'
    GROUP BY month ORDER BY month DESC LIMIT 6
")->fetchAll();

$totalApproved = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'Approved'")->fetchColumn();
$totalPending = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'Pending'")->fetchColumn();
$totalRejected = $pdo->query("SELECT COUNT(*) FROM properties WHERE status = 'Rejected'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <style>
        /* Override header text color to black */
        .reports-header h1,
        .reports-header p {
            color: #0f172a !important;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Page Header -->
<section class="page-header admin-header-small reports-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-12">
                <h1 class="fade-in">📊 Reports & Analytics</h1>
                <p class="fade-in">Platform insights and statistics</p>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card-new success fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <h3><?= number_format($totalApproved) ?></h3>
                    <p>Approved Properties</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-new warning fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <h3><?= number_format($totalPending) ?></h3>
                    <p>Pending Properties</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-new danger fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-times-circle"></i></div>
                <div class="stat-content">
                    <h3><?= number_format($totalRejected) ?></h3>
                    <p>Rejected Properties</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trend -->
    <?php if (!empty($monthlyStats)): ?>
        <div class="card-modern fade-in mb-4">
            <div class="card-modern-header">
                <i class="fas fa-chart-line text-primary"></i> Monthly Property Listings (Last 6 Months)
            </div>
            <div class="card-modern-body">
                <div class="row g-3">
                    <?php foreach ($monthlyStats as $stat): ?>
                        <div class="col-md-2 col-4 text-center">
                            <div class="month-bar" style="height: <?= max(20, ($stat['cnt'] / max(array_column($monthlyStats, 'cnt'))) * 100) ?>px;"></div>
                            <div class="month-label"><?= date('M Y', strtotime($stat['month'] . '-01')) ?></div>
                            <div class="month-count"><?= $stat['cnt'] ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Properties by Type -->
        <div class="col-md-6">
            <div class="card-modern fade-in">
                <div class="card-modern-header">
                    <i class="fas fa-building text-primary"></i> Properties by Type
                </div>
                <div class="card-modern-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Type</th><th>Count</th><th>Avg Price</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byType as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['property_type'] ?? 'Unknown') ?></td>
                                    <td><span class="badge bg-primary"><?= (int) $row['cnt'] ?></span></td>
                                    <td><?= formatPrice($row['avg_price']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Cities -->
        <div class="col-md-6">
            <div class="card-modern fade-in">
                <div class="card-modern-header">
                    <i class="fas fa-map-marker-alt text-primary"></i> Top Cities
                </div>
                <div class="card-modern-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>City</th><th>Listings</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($byCity as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['city']) ?></td>
                                    <td>
                                        <div class="progress" style="height:20px;">
                                            <div class="progress-bar bg-primary" style="width: <?= min(100, ($row['cnt'] / max(array_column($byCity, 'cnt'))) * 100) ?>%;">
                                                <?= $row['cnt'] ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>