<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('buyer');

$buyerId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT e.*, p.title AS property_title, p.city, p.price
    FROM enquiries e
    JOIN properties p ON e.property_id = p.id
    WHERE e.buyer_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$buyerId]);
$enquiries = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Enquiries - Buyer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>

<!-- Decorative shapes -->
<div class="page-shape page-shape-1"></div>
<div class="page-shape page-shape-2"></div>
<div class="page-shape page-shape-3"></div>

<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4 position-relative" style="z-index: 1;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fade-in">My Enquiries</h2>
        <a href="<?= BASE_URL ?>/search.php" class="btn btn-primary fade-in">Browse Properties</a>
    </div>

    <?php if (empty($enquiries)): ?>
        <div class="alert alert-info fade-in">You haven't sent any enquiries yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle fade-in glass-table">
                <thead class="table-light">
                    <tr><th>Property</th><th>City</th><th>Price</th><th>Message</th><th>Date</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($enquiries as $e): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/property.php?id=<?= (int) $e['property_id'] ?>"><?= htmlspecialchars($e['property_title']) ?></a></td>
                            <td><?= htmlspecialchars($e['city']) ?></td>
                            <td><?= formatPrice($e['price']) ?></td>
                            <td><?= htmlspecialchars($e['message']) ?></td>
                            <td><?= date('M j, Y', strtotime($e['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>