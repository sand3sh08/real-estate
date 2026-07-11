<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

$data = json_decode(file_get_contents('php://input'), true);
$conversationId = (int) ($data['conversation_id'] ?? 0);
$isTyping = (bool) ($data['is_typing'] ?? false);
$userId = (int) $_SESSION['user_id'];

if ($isTyping) {
    $stmt = $pdo->prepare("
        INSERT INTO typing_status (conversation_id, user_id, is_typing, updated_at)
        VALUES (?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE is_typing = 1, updated_at = NOW()
    ");
    $stmt->execute([$conversationId, $userId]);
} else {
    $pdo->prepare("DELETE FROM typing_status WHERE conversation_id = ? AND user_id = ?")
        ->execute([$conversationId, $userId]);
}

echo json_encode(['success' => true]);