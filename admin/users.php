<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $statusMap = [
        'approve' => 'active',
        'reject'  => 'rejected',
        'block'   => 'blocked',
        'activate'=> 'active',
    ];
    if (isset($statusMap[$action])) {
        $pdo->prepare('UPDATE users SET status = ? WHERE id = ? AND role != ?')
            ->execute([$statusMap[$action], $userId, 'admin']);
    }
    header('Location: users.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>
<main class="container py-4">
    <h2 class="mb-4 fade-in">Manage Users</h2>
    <div class="table-responsive">
        <table class="table table-hover align-middle fade-in">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['phone']) ?></td>
                        <td><span class="badge-role bg-secondary text-white"><?= htmlspecialchars($u['role']) ?></span></td>
                        <td><?= statusBadge($u['status']) ?></td>
                        <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['status'] === 'pending'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <button name="action" value="approve" class="btn btn-success btn-sm">Approve</button>
                                    <button name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>
                                </form>
                            <?php elseif ($u['status'] === 'active'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <button name="action" value="block" class="btn btn-dark btn-sm">Block</button>
                                </form>
                            <?php else: ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                    <button name="action" value="activate" class="btn btn-primary btn-sm">Activate</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>