<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('seller');

$sellerId = (int)$_SESSION['user_id'];
$sellerName = $_SESSION['name'] ?? 'Seller';

// All Stats for Seller
$stats = [
    'total'    => $pdo->prepare("SELECT COUNT(*) FROM properties WHERE seller_id = ?"),
    'pending'  => $pdo->prepare("SELECT COUNT(*) FROM properties WHERE seller_id = ? AND status = 'Pending'"),
    'approved' => $pdo->prepare("SELECT COUNT(*) FROM properties WHERE seller_id = ? AND status = 'Approved'"),
    'rejected' => $pdo->prepare("SELECT COUNT(*) FROM properties WHERE seller_id = ? AND status = 'Rejected'")
];

foreach ($stats as &$stmt) {
    $stmt->execute([$sellerId]);
    $stmt = $stmt->fetchColumn();
}
unset($stmt);

// Property type breakdown for this seller
$typeBreakdown = $pdo->prepare("
    SELECT property_type, COUNT(*) AS cnt
    FROM properties
    WHERE seller_id = ?
    GROUP BY property_type
");
$typeBreakdown->execute([$sellerId]);
$types = $typeBreakdown->fetchAll();

// Recent properties (last 3)
$recentProps = $pdo->prepare("
    SELECT p.*,
           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) AS thumb
    FROM properties p
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC LIMIT 3
");
$recentProps->execute([$sellerId]);
$recentProperties = $recentProps->fetchAll();

$totalApproved = $stats['approved'];
$totalPending = $stats['pending'];
$totalRejected = $stats['rejected'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Dashboard - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Page Header -->
<section class="page-header seller-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fade-in">👋 Welcome, <?= htmlspecialchars($sellerName) ?></h1>
                <p class="fade-in">Manage your properties and track your sales performance</p>
                <div class="header-stats fade-in">
                    <span><i class="fas fa-home text-warning"></i> <?= (int)$stats['total'] ?> Total</span>
                    <span><i class="fas fa-clock text-light"></i> <?= (int)$stats['pending'] ?> Pending</span>
                    <span><i class="fas fa-check-circle text-success"></i> <?= (int)$stats['approved'] ?> Approved</span>
                    <span><i class="fas fa-times-circle text-danger"></i> <?= (int)$stats['rejected'] ?> Rejected</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="add_property.php" class="btn btn-light btn-lg btn-header fade-in">
                    <i class="fas fa-plus-circle"></i> Add Property
                </a>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">

    <!-- Stats Grid - 4 Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card-new primary fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-home"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$stats['total'] ?></h3>
                    <p>Total Properties</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-new warning fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-clock"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$stats['pending'] ?></h3>
                    <p>Pending Approval</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-new success fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-check-circle"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$stats['approved'] ?></h3>
                    <p>Approved</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-new danger fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-times-circle"></i></div>
                <div class="stat-content">
                    <h3><?= (int)$stats['rejected'] ?></h3>
                    <p>Rejected</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="quick-stat fade-in">
                <div class="quick-stat-icon bg-success"><i class="fas fa-check-circle"></i></div>
                <div class="quick-stat-content">
                    <span class="label">Approved</span>
                    <span class="value"><?= number_format($totalApproved) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-stat fade-in">
                <div class="quick-stat-icon bg-warning"><i class="fas fa-clock"></i></div>
                <div class="quick-stat-content">
                    <span class="label">Pending</span>
                    <span class="value"><?= number_format($totalPending) ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-stat fade-in">
                <div class="quick-stat-icon bg-danger"><i class="fas fa-times-circle"></i></div>
                <div class="quick-stat-content">
                    <span class="label">Rejected</span>
                    <span class="value"><?= number_format($totalRejected) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Type Pills -->
    <?php if (!empty($types)): ?>
        <div class="type-pills fade-in mb-4">
            <span class="type-label"><i class="fas fa-tags"></i> Your Property Types:</span>
            <?php foreach ($types as $type): ?>
                <span class="type-pill">
                    <?= htmlspecialchars($type['property_type']) ?>
                    <span class="count"><?= $type['cnt'] ?></span>
                </span>
            <?php endforeach; ?>
            <span class="type-pill">
                Total <span class="count"><?= (int)$stats['total'] ?></span>
            </span>
        </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="action-grid fade-in">
        <a href="add_property.php" class="action-btn primary">
            <i class="fas fa-plus-circle"></i>
            <span>Add New Property</span>
        </a>
        <a href="my_properties.php" class="action-btn secondary">
            <i class="fas fa-list"></i>
            <span>View My Properties</span>
        </a>
        <a href="<?= BASE_URL ?>/brokers.php" class="action-btn success">
            <i class="fas fa-user-tie"></i>
            <span>View Brokers</span>
        </a>
    </div>

    <!-- Recent Properties -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card-modern fade-in">
                <div class="card-modern-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clock text-primary"></i> Recent Properties</span>
                    <a href="my_properties.php" class="btn btn-sm btn-outline-primary">View All →</a>
                </div>
                <div class="card-modern-body">
                    <?php if (empty($recentProperties)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No properties listed yet. Start by adding one!</p>
                            <a href="add_property.php" class="btn btn-primary btn-sm">Add Property</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentProperties as $prop): ?>
                            <div class="recent-item">
                                <?php if ($prop['thumb']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/property_images/<?= htmlspecialchars($prop['thumb']) ?>" class="recent-item-img" alt="">
                                <?php else: ?>
                                    <div class="recent-item-icon"><i class="fas fa-home"></i></div>
                                <?php endif; ?>
                                <div class="recent-item-content">
                                    <h6><?= htmlspecialchars($prop['title']) ?></h6>
                                    <p><?= formatPrice($prop['price']) ?> · <?= htmlspecialchars($prop['city']) ?></p>
                                </div>
                                <span class="badge-status <?= strtolower($prop['status']) ?>"><?= $prop['status'] ?></span>
                                <a href="<?= BASE_URL ?>/property.php?id=<?= (int) $prop['id'] ?>" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>