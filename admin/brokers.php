<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('broker');

$userId = (int) $_SESSION['user_id'];
$brokerId = $userId;

// Fetch all buyers
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM conversations c WHERE c.user_id = u.id AND c.broker_id = $brokerId AND c.user_role = 'buyer') AS has_conversation
    FROM users u
    WHERE u.role = 'buyer' AND u.status = 'active'
    ORDER BY u.name ASC
");
$buyers = $stmt->fetchAll();

// Get unread count for each buyer
$unreadCounts = [];
foreach ($buyers as $buyer) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM messages m
        JOIN conversations c ON m.conversation_id = c.id
        WHERE c.broker_id = ? AND c.user_id = ? AND c.user_role = 'buyer'
        AND m.sender_id != ? AND m.is_read = 0
    ");
    $stmt->execute([$brokerId, $buyer['id'], $brokerId]);
    $unreadCounts[$buyer['id']] = (int) $stmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buyers - Broker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Page Header -->
<section class="page-header seller-header" style="background: linear-gradient(135deg, #2563eb, #1e40af);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fade-in">👥 Buyers</h1>
                <p class="fade-in">Connect with potential buyers and offer your services</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="dashboard.php" class="btn btn-light btn-header fade-in">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">
    <?php if (empty($buyers)): ?>
        <div class="empty-state">
            <i class="fas fa-users" style="font-size: 60px; opacity: 0.2;"></i>
            <h4>No buyers yet</h4>
            <p>Buyers will appear here when they register on the platform.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($buyers as $buyer): ?>
                <?php 
                $unread = $unreadCounts[$buyer['id']] ?? 0;
                $hasConversation = $buyer['has_conversation'] > 0;
                ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card buyer-card fade-in">
                        <div class="card-body text-center">
                            <div class="buyer-avatar">
                                <?= strtoupper(substr($buyer['name'], 0, 2)) ?>
                            </div>
                            <h5 class="mt-2"><?= htmlspecialchars($buyer['name']) ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars($buyer['email']) ?></p>
                            <p class="text-muted small"><?= htmlspecialchars($buyer['phone'] ?? 'No phone') ?></p>
                            <div class="mt-2">
                                <a href="<?= BASE_URL ?>/messaging/inbox.php?buyer_id=<?= $buyer['id'] ?>&role=broker" 
                                   class="btn <?= $unread > 0 ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm w-100">
                                    <?php if ($unread > 0): ?>
                                        <i class="fas fa-comment"></i> <?= $unread ?> new
                                    <?php elseif ($hasConversation): ?>
                                        <i class="fas fa-comment-dots"></i> Continue Chat
                                    <?php else: ?>
                                        <i class="fas fa-comment"></i> Message Buyer
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<style>
    .buyer-card {
        border: none;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        transition: all 0.3s ease;
        background: white;
        height: 100%;
    }
    .buyer-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(37,99,235,0.12);
    }
    .buyer-avatar {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: linear-gradient(135deg, #dbeafe, #fef3c7);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-size: 28px;
        font-weight: 700;
        color: #2563eb;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }
    .empty-state i {
        color: #94a3b8;
    }
    .empty-state h4 {
        margin-top: 15px;
        color: #0f172a;
    }
    .empty-state p {
        color: #94a3b8;
    }
    @media (max-width: 768px) {
        .buyer-avatar {
            width: 60px;
            height: 60px;
            font-size: 22px;
        }
    }
</style>
</body>
</html>