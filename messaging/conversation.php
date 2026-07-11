<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messaging_functions.php';

$conversationId = (int) ($_GET['id'] ?? 0);
$userId = (int) $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Get conversation details using direct query
$stmt = $pdo->prepare("
    SELECT c.*, 
           u1.name AS user_name, u1.id AS user_id,
           u2.name AS broker_name, u2.id AS broker_id,
           p.title AS property_title
    FROM conversations c
    JOIN users u1 ON c.user_id = u1.id
    JOIN users u2 ON c.broker_id = u2.id
    LEFT JOIN properties p ON c.property_id = p.id
    WHERE c.id = ?
");
$stmt->execute([$conversationId]);
$conversation = $stmt->fetch();

if (!$conversation) {
    header('Location: inbox.php');
    exit;
}

// Verify user belongs to this conversation
if ($userRole === 'broker' && $conversation['broker_id'] != $userId) {
    header('Location: inbox.php');
    exit;
} elseif ($userRole !== 'broker' && $conversation['user_id'] != $userId) {
    header('Location: inbox.php');
    exit;
}

// Mark messages as read (direct update)
$pdo->prepare("UPDATE messages SET is_read = 1, read_at = NOW() WHERE conversation_id = ? AND sender_id != ? AND is_read = 0")
    ->execute([$conversationId, $userId]);

// Fetch messages directly
$stmt = $pdo->prepare("
    SELECT m.*, u.name AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$conversationId]);
$messages = $stmt->fetchAll();

// Get other participant info
if ($userRole === 'broker') {
    $otherUser = [
        'id' => $conversation['user_id'],
        'name' => $conversation['user_name'],
        'role' => $conversation['user_role']
    ];
} else {
    $otherUser = [
        'id' => $conversation['broker_id'],
        'name' => $conversation['broker_name'],
        'role' => 'broker'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conversation - RealEstate</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="<?= BASE_URL ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .messages-container {
            max-height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f8fafc;
        }
        .message-item {
            margin-bottom: 16px;
            max-width: 75%;
            clear: both;
        }
        .message-item.sent {
            float: right;
            margin-left: auto;
        }
        .message-item.received {
            float: left;
            margin-right: auto;
        }
        .message-item .message-bubble {
            padding: 12px 18px;
            border-radius: 18px;
            word-wrap: break-word;
            display: inline-block;
        }
        .message-item.sent .message-bubble {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            border-bottom-right-radius: 4px;
        }
        .message-item.received .message-bubble {
            background: #ffffff;
            color: #0f172a;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }
        .message-item .message-time {
            font-size: 11px;
            opacity: 0.6;
            margin-top: 4px;
            clear: both;
        }
        .message-item.sent .message-time {
            text-align: right;
        }
        .conversation-header-bar {
            padding: 16px 20px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
        }
        .typing-indicator {
            font-size: 13px;
            color: #94a3b8;
            padding: 5px 20px;
            display: none;
            clear: both;
        }
        .typing-indicator .dots::after {
            content: '...';
            animation: typingDots 1.5s steps(4) infinite;
        }
        @keyframes typingDots {
            0% { content: ''; }
            25% { content: '.'; }
            50% { content: '..'; }
            75% { content: '...'; }
            100% { content: ''; }
        }
        .messages-container::after {
            content: '';
            display: table;
            clear: both;
        }
        .no-messages {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .no-messages i {
            font-size: 48px;
            opacity: 0.2;
            display: block;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/navbar.php'; ?>

<main class="container py-4">
    <div class="card-modern fade-in">
        <!-- Header -->
        <div class="conversation-header-bar d-flex justify-content-between align-items-center">
            <div>
                <a href="inbox.php" class="btn btn-sm btn-outline-secondary me-2">← Back</a>
                <strong><?= htmlspecialchars($otherUser['name']) ?></strong>
                <span class="badge-role <?= $otherUser['role'] === 'broker' ? 'bg-warning' : 'bg-info' ?> text-white ms-1"><?= ucfirst($otherUser['role']) ?></span>
                <?php if ($conversation['property_title']): ?>
                    <small class="text-muted ms-2">· <?= htmlspecialchars($conversation['property_title']) ?></small>
                <?php endif; ?>
            </div>
            <div>
                <?php if ($conversation['property_id']): ?>
                    <a href="<?= BASE_URL ?>/property.php?id=<?= (int) $conversation['property_id'] ?>" class="btn btn-sm btn-outline-primary">View Property</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="messages-container" id="messagesContainer">
            <?php if (empty($messages)): ?>
                <div class="no-messages">
                    <i class="fas fa-comment-dots"></i>
                    <p>No messages yet. Start the conversation!</p>
                </div>
            <?php else: ?>
                <?php foreach ($messages as $msg): ?>
                    <div class="message-item <?= $msg['sender_id'] == $userId ? 'sent' : 'received' ?>">
                        <div class="message-bubble">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </div>
                        <div class="message-time">
                            <?= date('M j, Y g:i A', strtotime($msg['created_at'])) ?>
                            <?php if ($msg['sender_id'] == $userId && $msg['is_read']): ?>
                                <i class="fas fa-check-double text-primary ms-1" title="Read"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <div id="typingIndicator" class="typing-indicator">
                <span class="dots">Typing</span>
            </div>
        </div>

        <!-- Message Input -->
        <div class="p-3 border-top bg-white">
            <form id="messageForm" class="d-flex gap-2">
                <input type="hidden" name="conversation_id" value="<?= $conversationId ?>">
                <textarea name="message" id="messageInput" class="form-control" rows="2" placeholder="Type your message..." style="resize: none;"></textarea>
                <button type="submit" class="btn btn-primary" style="align-self: flex-end;">
                    <i class="fas fa-paper-plane"></i> Send
                </button>
            </form>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<script>
const conversationId = <?= $conversationId ?>;
const userId = <?= $userId ?>;
const messagesContainer = document.getElementById('messagesContainer');
const messageInput = document.getElementById('messageInput');
const messageForm = document.getElementById('messageForm');

// Get the last message ID from the page
let lastMessageId = 0;
<?php if (!empty($messages)): ?>
    lastMessageId = <?= end($messages)['id'] ?? 0 ?>;
<?php endif; ?>

function appendMessage(messageData, sent) {
    const noMsg = document.querySelector('.no-messages');
    if (noMsg) noMsg.remove();
    
    const div = document.createElement('div');
    div.className = 'message-item ' + (sent ? 'sent' : 'received');
    const time = new Date().toLocaleString();
    const messageText = typeof messageData === 'string' ? messageData : messageData.message;
    div.innerHTML = `
        <div class="message-bubble">${messageText.replace(/\n/g, '<br>')}</div>
        <div class="message-time">${time}</div>
    `;
    messagesContainer.insertBefore(div, document.getElementById('typingIndicator'));
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

// Send message
messageForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = messageInput.value.trim();
    if (!message) return;

    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            conversation_id: conversationId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            appendMessage(data, true);
            lastMessageId = data.message_id || lastMessageId;
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    });
});

// Poll for new messages
function checkNewMessages() {
    fetch('get_messages.php?conversation_id=' + conversationId + '&last_id=' + lastMessageId)
    .then(response => response.json())
    .then(data => {
        if (data.messages && data.messages.length) {
            data.messages.forEach(msg => {
                appendMessage(msg, false);
                if (msg.id > lastMessageId) {
                    lastMessageId = msg.id;
                }
            });
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    })
    .catch(error => console.log('Polling error:', error));
}

// Poll every 3 seconds
setInterval(checkNewMessages, 3000);

// Auto-scroll to bottom
messagesContainer.scrollTop = messagesContainer.scrollHeight;

// Typing indicator
messageInput.addEventListener('input', function() {
    fetch('check_typing.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            conversation_id: conversationId,
            is_typing: this.value.length > 0
        })
    });
});
</script>
</body>
</html>