<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messaging_functions.php';

$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// ==============================================
// CASE 1: BROKER → SELLER (from property page)
// ==============================================
if (isset($_GET['seller_id']) && isset($_GET['property_id']) && isset($_GET['role']) && $_GET['role'] === 'broker') {
    $sellerId = (int) $_GET['seller_id'];
    $propertyId = (int) $_GET['property_id'];
    $brokerId = (int) $_SESSION['user_id'];
    $brokerName = $_SESSION['name'] ?? 'Broker';
    
    // Check if conversation already exists
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE user_id = ? AND broker_id = ? AND property_id = ? AND user_role = 'seller'
    ");
    $stmt->execute([$sellerId, $brokerId, $propertyId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        header('Location: conversation.php?id=' . $existing['id']);
        exit;
    }
    
    // Create new conversation
    $stmt = $pdo->prepare("
        INSERT INTO conversations (user_id, broker_id, user_role, property_id, created_at)
        VALUES (?, ?, 'seller', ?, NOW())
    ");
    $stmt->execute([$sellerId, $brokerId, $propertyId]);
    $convId = (int) $pdo->lastInsertId();
    
    // Send intro message
    $introMessage = "Hello! I'm $brokerName, a real estate broker. I'd like to offer my services to help you sell this property at the best possible price with only 2% commission. Let's connect!";
    sendMessage($pdo, $convId, $brokerId, 'broker', $introMessage);
    
    header('Location: conversation.php?id=' . $convId);
    exit;
}

// ==============================================
// CASE 2: BUYER → SELLER (from property page)
// ==============================================
if (isset($_GET['seller_id']) && isset($_GET['property_id']) && !isset($_GET['role'])) {
    $sellerId = (int) $_GET['seller_id'];
    $propertyId = (int) $_GET['property_id'];
    $buyerId = (int) $_SESSION['user_id'];
    
    // Check if conversation already exists
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE user_id = ? AND broker_id = ? AND property_id = ? AND user_role = 'buyer'
    ");
    $stmt->execute([$buyerId, $sellerId, $propertyId]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        header('Location: conversation.php?id=' . $existing['id']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO conversations (user_id, broker_id, user_role, property_id, created_at)
        VALUES (?, ?, 'buyer', ?, NOW())
    ");
    $stmt->execute([$buyerId, $sellerId, $propertyId]);
    $convId = (int) $pdo->lastInsertId();
    header('Location: conversation.php?id=' . $convId);
    exit;
}

// ==============================================
// CASE 3: BUYER/SELLER → BROKER (from brokers page)
// ==============================================
if (isset($_GET['broker_id']) && !isset($_GET['new'])) {
    $brokerId = (int) $_GET['broker_id'];
    $userId = (int) $_SESSION['user_id'];
    $userRole = $_SESSION['role'];
    
    // Check if conversation already exists
    $stmt = $pdo->prepare("
        SELECT id FROM conversations 
        WHERE user_id = ? AND broker_id = ? AND user_role = ?
    ");
    $stmt->execute([$userId, $brokerId, $userRole]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        header('Location: conversation.php?id=' . $existing['id']);
        exit;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO conversations (user_id, broker_id, user_role, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    $stmt->execute([$userId, $brokerId, $userRole]);
    $convId = (int) $pdo->lastInsertId();
    header('Location: conversation.php?id=' . $convId);
    exit;
}

// ==============================================
// FETCH CONVERSATIONS – DIRECT DATABASE QUERY
// ==============================================

// 1. Try the function first
$conversations = getUserConversations($pdo, $userId, $userRole);
$unreadCount = getUnreadCount($pdo, $userId);

// 2. If function returns empty, use direct query (fallback)
if (empty($conversations)) {
    // Direct query for seller, buyer, or broker
    if ($userRole === 'broker') {
        $stmt = $pdo->prepare("
            SELECT c.*, u.name AS user_name, 
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND is_read = 0 AND sender_id != ?) AS unread_count,
                   p.title AS property_title
            FROM conversations c
            JOIN users u ON c.user_id = u.id
            LEFT JOIN properties p ON c.property_id = p.id
            WHERE c.broker_id = ?
            ORDER BY c.last_message_at DESC
        ");
        $stmt->execute([$userId, $userId]);
        $conversations = $stmt->fetchAll();
    } else {
        // For seller or buyer
        $stmt = $pdo->prepare("
            SELECT c.*, u.name AS broker_name,
                   (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND is_read = 0 AND sender_id != ?) AS unread_count,
                   p.title AS property_title
            FROM conversations c
            JOIN users u ON c.broker_id = u.id
            LEFT JOIN properties p ON c.property_id = p.id
            WHERE c.user_id = ? AND c.user_role = ?
            ORDER BY c.last_message_at DESC
        ");
        $stmt->execute([$userId, $userId, $userRole]);
        $conversations = $stmt->fetchAll();
    }
    
    // Recalculate unread count
    $unreadCount = getUnreadCount($pdo, $userId);
}

// If still empty, reset unread count
if (empty($conversations) && $unreadCount > 0) {
    $pdo->prepare("UPDATE messages m 
                   JOIN conversations c ON m.conversation_id = c.id 
                   SET m.is_read = 1 
                   WHERE (c.broker_id = ? OR c.user_id = ?) 
                   AND m.sender_id != ? AND m.is_read = 0")
        ->execute([$userId, $userId, $userId]);
    $unreadCount = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">

    <style>
        .conversation-item {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .conversation-item:hover { background: #f8fafc; }
        .conversation-item.unread { background: #eff6ff; }
        .conversation-item.unread:hover { background: #dbeafe; }
        .conversation-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #dbeafe, #fef3c7);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .conversation-avatar .avatar-text {
            font-size: 20px;
            font-weight: 700;
            color: #2563eb;
        }
        .conversation-content { flex: 1; min-width: 0; }
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        .conversation-header h6 {
            font-weight: 700;
            color: #0f172a;
            margin: 0;
            font-size: 15px;
        }
        .conversation-header h6 .badge-role {
            font-size: 10px;
            padding: 2px 10px;
            border-radius: 30px;
        }
        .conversation-header small {
            font-size: 12px;
            color: #94a3b8;
        }
        .last-message {
            color: #64748b;
            font-size: 14px;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .last-message .badge {
            font-size: 11px;
            padding: 3px 8px;
        }
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { color: #94a3b8; }
        .empty-state h4 { margin-top: 15px; color: #0f172a; }
        .empty-state p { color: #94a3b8; }

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
        .page-header h1 { font-weight: 800; font-size: 36px; position: relative; z-index: 1; }
        .page-header p { font-size: 18px; opacity: 0.85; position: relative; z-index: 1; }
        .btn-header {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 700;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }
        .btn-header:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0,0,0,0.2); }

        @media (max-width: 768px) {
            .page-header { padding: 30px 0 25px; text-align: center; }
            .page-header h1 { font-size: 26px; }
            .page-header p { font-size: 15px; }
            .btn-header { padding: 10px 20px; font-size: 14px; }
            .conversation-item { padding: 12px 15px; }
            .conversation-avatar { width: 40px; height: 40px; }
            .conversation-avatar .avatar-text { font-size: 16px; }
            .conversation-header h6 { font-size: 14px; }
            .last-message { font-size: 13px; }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<section class="page-header seller-header" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="fade-in">💬 Messages</h1>
                <p class="fade-in"><?= $unreadCount ?> unread messages</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="<?= BASE_URL ?>/search.php" class="btn btn-light btn-header fade-in">
                    <i class="fas fa-search"></i> Browse Properties
                </a>
            </div>
        </div>
    </div>
</section>

<main class="container py-4">
    <?php if (empty($conversations)): ?>
        <div class="empty-state">
            <i class="fas fa-inbox" style="font-size: 60px; opacity: 0.2;"></i>
            <h4>No conversations yet</h4>
            <p>Start chatting with brokers, sellers, or buyers about properties you're interested in.</p>
            <a href="<?= BASE_URL ?>/search.php" class="btn btn-primary">Browse Properties</a>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="card-modern fade-in">
                    <div class="card-modern-body p-0">
                        <?php foreach ($conversations as $conv): ?>
                            <a href="conversation.php?id=<?= (int) $conv['id'] ?>" class="text-decoration-none">
                                <div class="conversation-item <?= ($conv['unread_count'] ?? 0) > 0 ? 'unread' : '' ?>">
                                    <div class="conversation-avatar">
                                        <?php if ($userRole === 'broker'): ?>
                                            <span class="avatar-text"><?= strtoupper(substr($conv['user_name'] ?? 'U', 0, 2)) ?></span>
                                        <?php else: ?>
                                            <span class="avatar-text"><?= strtoupper(substr($conv['broker_name'] ?? 'B', 0, 2)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="conversation-content">
                                        <div class="conversation-header">
                                            <h6>
                                                <?php if ($userRole === 'broker'): ?>
                                                    <?= htmlspecialchars($conv['user_name'] ?? 'Unknown User') ?>
                                                    <span class="badge badge-role bg-info text-white"><?= $conv['user_role'] ?? 'User' ?></span>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($conv['broker_name'] ?? 'Unknown Broker') ?>
                                                    <span class="badge badge-role bg-warning text-white">Broker</span>
                                                <?php endif; ?>
                                                <?php if ($conv['property_title']): ?>
                                                    <small class="text-muted">· <?= htmlspecialchars($conv['property_title']) ?></small>
                                                <?php endif; ?>
                                            </h6>
                                            <small class="text-muted"><?= date('M j, Y', strtotime($conv['last_message_at'] ?? $conv['created_at'])) ?></small>
                                        </div>
                                        <p class="last-message">
                                            <?= htmlspecialchars($conv['last_message'] ?? 'No messages yet') ?>
                                            <?php if (($conv['unread_count'] ?? 0) > 0): ?>
                                                <span class="badge bg-danger ms-2"><?= $conv['unread_count'] ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>