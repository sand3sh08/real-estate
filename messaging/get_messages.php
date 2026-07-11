<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/messaging_functions.php';

$conversationId = (int) ($_GET['conversation_id'] ?? 0);
$lastId = (int) ($_GET['last_id'] ?? 0);
$userId = (int) $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT m.*, u.name AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.conversation_id = ? AND m.id > ? AND m.sender_id != ?
    ORDER BY m.created_at ASC
");
$stmt->execute([$conversationId, $lastId, $userId]);
$messages = $stmt->fetchAll();

echo json_encode(['messages' => $messages]);