<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('broker');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - Broker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4">Clients</h2>
    <div class="alert alert-info">
        Client management can be extended here to track buyers and sellers you assist.
        Register buyers and sellers through the platform to get started.
    </div>
    <a href="dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
