<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$tab = $_GET['tab'] ?? 'pending';
$allowed = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($tab, $allowed, true)) {
    $tab = 'pending';
}

$sql = "
    SELECT p.*, u.name AS seller_name,
           (SELECT image FROM property_images WHERE property_id = p.id LIMIT 1) AS thumb
    FROM properties p
    JOIN users u ON p.seller_id = u.id
";
if ($tab !== 'all') {
    $sql .= " WHERE p.status = ?";
    $params = [ucfirst($tab)];
} else {
    $params = [];
}
$sql .= ' ORDER BY p.created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Properties - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">Manage Properties</h2>
    <ul class="nav nav-tabs mb-4">
        <?php foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $key => $label): ?>
            <li class="nav-item">
                <a class="nav-link <?= $tab === $key ? 'active' : '' ?>" href="?tab=<?= $key ?>"><?= $label ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <div class="table-responsive">
        <table class="table table-hover align-middle fade-in">
            <thead class="table-light">
                <tr>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Seller</th>
                    <th>Price</th>
                    <th>City</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($properties)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No properties found.</td></tr>
                <?php else: ?>
                    <?php foreach ($properties as $p): ?>
                        <tr>
                            <td>
                                <?php if ($p['thumb']): ?>
                                    <img src="<?= BASE_URL ?>/uploads/property_images/<?= htmlspecialchars($p['thumb']) ?>" class="table-thumb" alt="">
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($p['title']) ?></td>
                            <td><?= htmlspecialchars($p['seller_name']) ?></td>
                            <td><?= formatPrice($p['price']) ?></td>
                            <td><?= htmlspecialchars($p['city']) ?></td>
                            <td><?= statusBadge($p['status']) ?></td>
                            <td>
                                <?php if ($p['status'] === 'Pending'): ?>
                                    <a href="approve.php?id=<?= (int) $p['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                    <a href="reject.php?id=<?= (int) $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Reject this property?')">Reject</a>
                                <?php else: ?>
                                    <a href="<?= BASE_URL ?>/property.php?id=<?= (int) $p['id'] ?>" class="btn btn-outline-primary btn-sm">View</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>