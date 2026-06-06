<?php
function getMessages($pdo, $userId, $otherUserId = null, $limit = 50) {
    if ($otherUserId) {
        $stmt = $pdo->prepare("
            SELECT m.*, u.first_name, u.last_name, u.role
            FROM messages m
            JOIN users u ON (m.sender_id = u.id)
            WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
            LIMIT ?
        ");
        $stmt->execute([$userId, $otherUserId, $otherUserId, $userId, $limit]);
    } else {
        $stmt = $pdo->prepare("
            SELECT m.*, u.first_name, u.last_name, u.role
            FROM messages m
            JOIN users u ON (m.sender_id = u.id)
            WHERE m.sender_id = ? OR m.receiver_id = ?
            ORDER BY m.created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$userId, $userId, $limit]);
    }
    return $stmt->fetchAll();
}

function getConversations($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS other_user_id,
            u.first_name, u.last_name, u.role,
            (SELECT message FROM messages WHERE (sender_id = ? AND receiver_id = other_user_id) OR (sender_id = other_user_id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) AS last_message,
            (SELECT created_at FROM messages WHERE (sender_id = ? AND receiver_id = other_user_id) OR (sender_id = other_user_id AND receiver_id = ?) ORDER BY created_at DESC LIMIT 1) AS last_time
        FROM messages m
        JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
        WHERE m.sender_id = ? OR m.receiver_id = ?
        ORDER BY last_time DESC
    ");
    $stmt->execute([$userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId]);
    return $stmt->fetchAll();
}

function sendMessage($pdo, $senderId, $receiverId, $message, $appointmentId = null) {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, appointment_id, message) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$senderId, $receiverId, $appointmentId, $message]);
}

function getUnreadMessageCount($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = FALSE");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn();
}

function markMessagesRead($pdo, $userId, $otherUserId) {
    $stmt = $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE receiver_id = ? AND sender_id = ?");
    return $stmt->execute([$userId, $otherUserId]);
}

function getMessagePartnerUserIds($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT DISTINCT CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_id
        FROM messages WHERE sender_id = ? OR receiver_id = ?
    ");
    $stmt->execute([$userId, $userId, $userId]);
    return array_column($stmt->fetchAll(), 'other_id');
}
