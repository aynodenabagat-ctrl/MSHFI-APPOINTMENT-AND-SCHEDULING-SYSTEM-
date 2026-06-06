<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();
if (!$doctor) { die('Doctor profile not found.'); }

$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ? AND doctor_id = ? AND status IN ('pending', 'confirmed')");
    $stmt->execute([$id, $doctor['id']]);
}

header('Location: dashboard.php');
exit;
