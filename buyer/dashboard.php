<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('buyer');

$buyerId = (int) $_SESSION['user_id'];

$favCount = $pdo->prepare('SELECT COUNT(*) FROM favourites WHERE buyer_id = ?');
$favCount->execute([$buyerId]);
$enqCount = $pdo->prepare('SELECT COUNT(*) FROM enquiries WHERE buyer_id = ?');
$enqCount->execute([$buyerId]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyer Dashboard - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">Buyer Dashboard</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card stat-card fade-in">
                <div class="card-body">
                    <h6>Saved Favourites</h6>
                    <h3><?= (int) $favCount->fetchColumn() ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card stat-card fade-in">
                <div class="card-body">
                    <h6>Enquiries Sent</h6>
                    <h3><?= (int) $enqCount->fetchColumn() ?></h3>
                </div>
            </div>
        </div>
    </div>
    <a href="<?= BASE_URL ?>/search.php" class="btn btn-primary me-2 fade-in">Browse Properties</a>
    <a href="favourites.php" class="btn btn-outline-primary me-2 fade-in">My Favourites</a>
    <a href="<?= BASE_URL ?>/brokers.php" class="btn btn-success fade-in">👨‍💼 View Brokers</a>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
