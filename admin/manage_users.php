<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

$role = $_GET['role'] ?? 'seller';
if (!in_array($role, ['seller','buyer','broker'], true)) $role = 'seller';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_POST['user_id'];
    if ($_POST['do'] === 'block') {
        $pdo->prepare("UPDATE users SET status='Blocked' WHERE id=?")->execute([$uid]);
    } elseif ($_POST['do'] === 'unblock') {
        $pdo->prepare("UPDATE users SET status='Active' WHERE id=?")->execute([$uid]);
    } elseif ($_POST['do'] === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
    }
    header('Location: manage_users.php?role=' . $role);
    exit;
}

if ($role === 'broker') {
    $rows = $pdo->query("SELECT u.*, b.experience, b.commission, b.company FROM users u
                          JOIN brokers b ON b.user_id = u.id WHERE u.role='broker' ORDER BY u.created_at DESC")->fetchAll();
} else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
    $stmt->execute([$role]);
    $rows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage <?= h($role) ?>s — Admin — Ledger &amp; Key</title>
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<div class="container" style="padding-top:32px;padding-bottom:60px;">
  <div class="dash-shell">
    <aside class="side-nav">
      <a href="dashboard.php">Overview</a>
      <a href="properties.php?status=Pending">Pending properties</a>
      <a href="properties.php?status=Approved">Approved properties</a>
      <a href="properties.php?status=Rejected">Rejected properties</a>
      <a href="manage_users.php?role=seller" class="<?= $role==='seller'?'active':'' ?>">Manage sellers</a>
      <a href="manage_users.php?role=buyer" class="<?= $role==='buyer'?'active':'' ?>">Manage buyers</a>
      <a href="manage_users.php?role=broker" class="<?= $role==='broker'?'active':'' ?>">Manage brokers</a>
    </aside>
    <main>
      <h2 style="margin-bottom:20px;text-transform:capitalize;"><?= h($role) ?>s</h2>

      <?php if ($rows): ?>
      <table>
        <tr>
          <th>Name</th><th>Email</th><th>Phone</th>
          <?php if ($role === 'broker'): ?><th>Experience</th><th>Commission</th><th>Company</th><?php endif; ?>
          <th>Status</th><th>Joined</th><th>Actions</th>
        </tr>
        <?php foreach ($rows as $u): ?>
        <tr>
          <td><?= h($u['name']) ?></td>
          <td><?= h($u['email']) ?></td>
          <td><?= h($u['phone']) ?></td>
          <?php if ($role === 'broker'): ?>
            <td><?= (int)$u['experience'] ?> yrs</td>
            <td><?= h($u['commission']) ?>%</td>
            <td><?= h($u['company']) ?></td>
          <?php endif; ?>
          <td><span class="status-pill status-<?= $u['status']==='Blocked'?'Rejected':'Approved' ?>" style="position:static;"><?= h($u['status']) ?></span></td>
          <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td style="display:flex;gap:6px;">
            <form method="POST">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <?php if ($u['status'] === 'Blocked'): ?>
                <button name="do" value="unblock" class="btn btn-approve btn-sm">Unblock</button>
              <?php else: ?>
                <button name="do" value="block" class="btn btn-line btn-sm">Block</button>
              <?php endif; ?>
            </form>
            <form method="POST" onsubmit="return confirm('Delete this account permanently?')">
              <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
              <button name="do" value="delete" class="btn btn-danger btn-sm">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?>
        <div class="empty-state"><h3>No <?= h($role) ?>s yet</h3></div>
      <?php endif; ?>
    </main>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
