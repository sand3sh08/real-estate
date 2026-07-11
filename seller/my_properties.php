<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('seller');

$sellerId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT p.*,
           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) AS thumb
    FROM properties p
    WHERE p.seller_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$sellerId]);
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Properties - Seller</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Decorative floating shapes -->
<div class="page-shape page-shape-1"></div>
<div class="page-shape page-shape-2"></div>
<div class="page-shape page-shape-3"></div>

<main class="container py-4 position-relative" style="z-index: 1;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0 fade-in">My Properties</h2>
        <a href="add_property.php" class="btn btn-primary fade-in">➕ Add Property</a>
    </div>
    <?php if (empty($properties)): ?>
        <div class="alert alert-info fade-in">You haven't listed any properties yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle fade-in">
                <thead>
                    <tr><th>Image</th><th>Title</th><th>Price</th><th>City</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($properties as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['thumb']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/property_images/<?= htmlspecialchars($p['thumb']) ?>" class="table-thumb" alt="">
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['title']) ?></td>
                            <td><?= formatPrice($p['price']) ?></td>
                            <td><?= htmlspecialchars($p['city']) ?></td>
                            <td><span class="badge-status <?= strtolower($p['status']) === 'approved' ? 'bg-success' : 'bg-warning' ?>"><?= $p['status'] ?></span></td>
                            <td>
                                <a href="edit_property.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline-primary btn-sm">Edit</a>
                                <a href="delete_property.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this property?')">Delete</a>
                            </td>
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