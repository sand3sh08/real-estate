<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

$stmt = $pdo->query("
    SELECT u.*, COUNT(p.id) AS property_count,
           SUM(CASE WHEN p.status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
           SUM(CASE WHEN p.status = 'Pending' THEN 1 ELSE 0 END) AS pending_count
    FROM users u
    LEFT JOIN properties p ON p.seller_id = u.id
    WHERE u.role = 'seller'
    GROUP BY u.id
    ORDER BY property_count DESC
");
$sellers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sellers - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Page Header -->
<section class="page-header admin-header-small">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fade-in">🏢 Sellers</h1>
                <p class="fade-in"><?= number_format(count($sellers)) ?> registered sellers</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="users.php?role=seller" class="btn btn-light btn-sm btn-header fade-in">
                    <i class="fas fa-users-cog"></i> Manage Sellers
                </a>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle fade-in">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Properties</th>
                    <th>Approved</th>
                    <th>Pending</th>
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                <?php $i = 1; foreach ($sellers as $s): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
                        <td><?= htmlspecialchars($s['email']) ?></td>
                        <td><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                        <td><?= statusBadge($s['status']) ?></td>
                        <td><span class="badge bg-secondary"><?= (int) $s['property_count'] ?></span></td>
                        <td><span class="badge bg-success"><?= (int) $s['approved_count'] ?></span></td>
                        <td><span class="badge bg-warning"><?= (int) $s['pending_count'] ?></span></td>
                        <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>