<?php

function getUnreadCount($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM messages m
            JOIN conversations c ON m.conversation_id = c.id
            WHERE (c.broker_id = ? OR c.user_id = ?)
            AND m.sender_id != ?
            AND m.is_read = 0
        ");
        $stmt->execute([$userId, $userId, $userId]);
        return (int) $stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getUserConversations($pdo, $userId, $role) {
    try {
        // FOR BROKER
        if ($role === 'broker') {
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
        } 
        // FOR SELLER – SIMPLIFIED & ROBUST
        else if ($role === 'seller') {
            $stmt = $pdo->prepare("
                SELECT c.*, 
                       u.name AS broker_name,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND is_read = 0 AND sender_id != ?) AS unread_count,
                       p.title AS property_title
                FROM conversations c
                JOIN users u ON c.broker_id = u.id
                LEFT JOIN properties p ON c.property_id = p.id
                WHERE c.user_id = ? AND c.user_role = 'seller'
                ORDER BY c.last_message_at DESC
            ");
            $stmt->execute([$userId, $userId]);
        } 
        // FOR BUYER
        else if ($role === 'buyer') {
            $stmt = $pdo->prepare("
                SELECT c.*, u.name AS broker_name,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND is_read = 0 AND sender_id != ?) AS unread_count,
                       p.title AS property_title
                FROM conversations c
                JOIN users u ON c.broker_id = u.id
                LEFT JOIN properties p ON c.property_id = p.id
                WHERE c.user_id = ? AND c.user_role = 'buyer'
                ORDER BY c.last_message_at DESC
            ");
            $stmt->execute([$userId, $userId]);
        } else {
            return [];
        }
        return $stmt->fetchAll();
    } catch (Exception $e) {
        // Log the error
        error_log("getUserConversations error: " . $e->getMessage());
        return [];
    }
}

function getConversationMessages($pdo, $conversationId, $userId, $limit = 50) {
    try {
        $stmt = $pdo->prepare("
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.conversation_id = ?
            ORDER BY m.created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$conversationId, $limit]);
        return $stmt->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

function getConversationParticipants($pdo, $conversationId) {
    try {
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
        return $stmt->fetch();
    } catch (Exception $e) {
        return null;
    }
}

function sendMessage($pdo, $conversationId, $senderId, $senderRole, $message) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (conversation_id, sender_id, sender_role, message, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$conversationId, $senderId, $senderRole, $message]);
        $messageId = (int) $pdo->lastInsertId();
        
        $pdo->prepare("
            UPDATE conversations 
            SET last_message = ?, last_message_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ")->execute([$message, $conversationId]);
        
        return $messageId;
    } catch (Exception $e) {
        return false;
    }
}

function markMessagesAsRead($pdo, $conversationId, $userId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET is_read = 1, read_at = NOW()
            WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
        ");
        return $stmt->execute([$conversationId, $userId]);
    } catch (Exception $e) {
        return false;
    }
}