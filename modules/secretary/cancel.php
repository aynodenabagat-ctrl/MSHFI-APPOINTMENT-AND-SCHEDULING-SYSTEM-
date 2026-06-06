<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['secretary', 'admin']);

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    if (validateCsrfToken($_POST['_csrf_token'] ?? '')) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ? AND status IN ('pending', 'confirmed')");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['message' => 'Appointment cancelled.', 'type' => 'error'];
    }
}

$redirect = 'dashboard.php';
if (!empty($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'appointments.php')) {
    $redirect = 'appointments.php';
}
header("Location: $redirect");
exit;
