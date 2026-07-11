<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('broker');

$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare('SELECT * FROM brokers WHERE user_id = ?');
$stmt->execute([$userId]);
$broker = $stmt->fetch();

// Fetch clients (buyers and sellers who have interacted with this broker)
$clients = $pdo->prepare("
    SELECT DISTINCT c.user_id, c.user_role, u.name, u.email, u.phone,
           (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != ? AND is_read = 0) AS unread_count,
           c.last_message, c.last_message_at, c.id AS conversation_id
    FROM conversations c
    JOIN users u ON c.user_id = u.id
    WHERE c.broker_id = ?
    ORDER BY c.last_message_at DESC
");
$clients->execute([$userId, $userId]);
$clients = $clients->fetchAll();

$totalClients = count($clients);

$unreadStmt = $pdo->prepare("
    SELECT COUNT(*) FROM messages m
    JOIN conversations c ON m.conversation_id = c.id
    WHERE c.broker_id = ? AND m.sender_id != ? AND m.is_read = 0
");
$unreadStmt->execute([$userId, $userId]);
$unreadCount = (int) $unreadStmt->fetchColumn();

// Count total buyers (for the "View Buyers" button)
$buyerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'buyer' AND status = 'active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Broker Dashboard - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <style>
        .fade-in {
            animation: fadeUp 0.8s ease forwards;
            opacity: 0;
        }

        .stat-card-new {
            background: white;
            border-radius: 20px;
            padding: 20px 25px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(0,0,0,0.04);
        }
        .stat-card-new:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        }
        .stat-card-new .stat-icon-wrap {
            width: 55px;
            height: 55px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .stat-card-new.primary .stat-icon-wrap { background: #dbeafe; color: #2563eb; }
        .stat-card-new.warning .stat-icon-wrap { background: #fef3c7; color: #f59e0b; }
        .stat-card-new.success .stat-icon-wrap { background: #d1fae5; color: #10b981; }
        .stat-card-new.info .stat-icon-wrap { background: #cffafe; color: #06b6d4; }
        .stat-card-new .stat-content h6 {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            line-height: 1.2;
        }
        .stat-card-new .stat-content p {
            color: #64748b;
            font-size: 13px;
            font-weight: 500;
            margin: 0;
        }

        .card-modern {
            background: white;
            border-radius: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.04);
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        .card-modern:hover {
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
        }
        .card-modern-header {
            padding: 18px 24px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            font-size: 16px;
            color: #0f172a;
        }
        .card-modern-body {
            padding: 16px 24px 24px;
        }

        .recent-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .recent-item:last-child {
            border-bottom: none;
        }
        .recent-item-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #475569;
            flex-shrink: 0;
        }
        .recent-item-content {
            flex: 1;
            min-width: 0;
        }
        .recent-item-content h6 {
            font-weight: 700;
            font-size: 15px;
            color: #0f172a;
            margin: 0;
        }
        .recent-item-content p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
        }

        .badge-role {
            padding: 3px 10px;
            border-radius: 30px;
            font-size: 11px;
            font-weight: 600;
        }

        .empty-state {
            text-align: center;
            padding: 30px 0;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 40px;
            opacity: 0.3;
            margin-bottom: 10px;
            display: block;
        }

        .page-header {
            padding: 50px 0 40px;
            color: white;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 30px 30px;
        }
        .page-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.08), transparent 60%);
        }
        .page-header h1 {
            font-weight: 800;
            font-size: 36px;
            position: relative;
            z-index: 1;
        }
        .page-header p {
            font-size: 18px;
            opacity: 0.85;
            position: relative;
            z-index: 1;
        }
        .btn-header {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        .btn-header:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .action-btn:hover {
            transform: translateY(-4px);
            text-decoration: none;
        }
        .action-btn i { font-size: 22px; }

        .action-btn.primary {
            background: #eff6ff;
            color: #2563eb;
            border-color: #dbeafe;
        }
        .action-btn.primary:hover {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
            box-shadow: 0 10px 30px rgba(37,99,235,0.3);
        }

        .action-btn.success {
            background: #ecfdf5;
            color: #10b981;
            border-color: #d1fae5;
        }
        .action-btn.success:hover {
            background: #10b981;
            color: white;
            border-color: #10b981;
            box-shadow: 0 10px 30px rgba(16,185,129,0.3);
        }

        .action-btn.info {
            background: #ecfeff;
            color: #06b6d4;
            border-color: #cffafe;
        }
        .action-btn.info:hover {
            background: #06b6d4;
            color: white;
            border-color: #06b6d4;
            box-shadow: 0 10px 30px rgba(6,182,212,0.3);
        }

        .header-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .header-stats span {
            font-size: 14px;
            opacity: 0.85;
        }
        .header-stats i {
            margin-right: 6px;
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 30px 0 25px;
                text-align: center;
            }
            .page-header h1 {
                font-size: 26px;
            }
            .page-header p {
                font-size: 15px;
            }
            .btn-header {
                padding: 10px 20px;
                font-size: 14px;
            }
            .stat-card-new {
                padding: 15px 18px;
            }
            .stat-card-new .stat-content h6 {
                font-size: 16px;
            }
            .stat-card-new .stat-icon-wrap {
                width: 45px;
                height: 45px;
                font-size: 20px;
            }
            .action-grid {
                grid-template-columns: 1fr 1fr;
            }
            .header-stats {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<!-- Page Header -->
<section class="page-header seller-header" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fade-in">👋 Welcome, <?= htmlspecialchars($_SESSION['name']) ?></h1>
                <p class="fade-in">Manage your clients and grow your network</p>
                <div class="header-stats fade-in">
                    <span><i class="fas fa-users"></i> <?= $totalClients ?> Clients</span>
                    <span><i class="fas fa-envelope"></i> <?= $unreadCount ?> Unread</span>
                    <span><i class="fas fa-user-tie"></i> <?= $buyerCount ?> Buyers</span>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="profile.php" class="btn btn-light btn-header fade-in">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">

    <!-- Quick Actions -->
    <div class="action-grid fade-in">
        <a href="buyers.php" class="action-btn primary">
            <i class="fas fa-users"></i>
            <span>View All Buyers</span>
        </a>
        <a href="<?= BASE_URL ?>/messaging/inbox.php" class="action-btn success">
            <i class="fas fa-envelope"></i>
            <span>Messages</span>
        </a>
    </div>

    <!-- Broker Profile Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card-new primary fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-building"></i></div>
                <div class="stat-content">
                    <h6><?= htmlspecialchars($broker['company'] ?? 'Not set') ?></h6>
                    <p>Company</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-new warning fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-briefcase"></i></div>
                <div class="stat-content">
                    <h6><?= htmlspecialchars($broker['experience'] ?? '0') ?> yrs</h6>
                    <p>Experience</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-new success fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-percentage"></i></div>
                <div class="stat-content">
                    <h6><?= htmlspecialchars($broker['commission'] ?? '0') ?>%</h6>
                    <p>Commission</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card-new info fade-in">
                <div class="stat-icon-wrap"><i class="fas fa-users"></i></div>
                <div class="stat-content">
                    <h6><?= $totalClients ?></h6>
                    <p>Clients</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Clients / Conversations -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card-modern fade-in">
                <div class="card-modern-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-users text-primary"></i> Your Clients</span>
                    <span class="text-muted small"><?= $totalClients ?> conversations</span>
                </div>
                <div class="card-modern-body">
                    <?php if (empty($clients)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>No conversations yet. Start connecting with buyers!</p>
                            <a href="buyers.php" class="btn btn-primary btn-sm">View Buyers</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($clients as $client): ?>
                            <div class="recent-item">
                                <div class="recent-item-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="recent-item-content">
                                    <h6><?= htmlspecialchars($client['name']) ?></h6>
                                    <p>
                                        <span class="badge-role <?= $client['user_role'] === 'buyer' ? 'bg-primary' : 'bg-success' ?> text-white">
                                            <?= ucfirst($client['user_role']) ?>
                                        </span>
                                        <?php if ($client['last_message']): ?>
                                            · <?= htmlspecialchars(substr($client['last_message'], 0, 40)) ?>...
                                        <?php endif; ?>
                                        <?php if ($client['unread_count'] > 0): ?>
                                            <span class="badge bg-danger ms-1"><?= $client['unread_count'] ?> new</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <a href="<?= BASE_URL ?>/messaging/conversation.php?id=<?= (int) $client['conversation_id'] ?>" 
                                   class="btn <?= $client['unread_count'] > 0 ? 'btn-primary' : 'btn-outline-primary' ?> btn-sm">
                                    <i class="fas fa-comment"></i> Message
                                </a>
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