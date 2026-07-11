<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messaging_functions.php';

$data = json_decode(file_get_contents('php://input'), true);
$conversationId = (int) ($data['conversation_id'] ?? 0);
$message = trim($data['message'] ?? '');
$senderId = (int) $_SESSION['user_id'];
$senderRole = $_SESSION['role'];

if (!$message || !$conversationId) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Verify user belongs to this conversation
$conv = getConversationParticipants($pdo, $conversationId);
if (!$conv) {
    echo json_encode(['success' => false, 'error' => 'Conversation not found']);
    exit;
}

if ($senderRole === 'broker' && $conv['broker_id'] != $senderId) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
} elseif ($senderRole !== 'broker' && $conv['user_id'] != $senderId) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$messageId = sendMessage($pdo, $conversationId, $senderId, $senderRole, $message);

echo json_encode([
    'success' => true,
    'message' => $message,
    'message_id' => $messageId
]);