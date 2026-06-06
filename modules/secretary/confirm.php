<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('secretary');

require_once __DIR__ . '/../../config/database.php';
$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
}

header('Location: dashboard.php');
exit;
