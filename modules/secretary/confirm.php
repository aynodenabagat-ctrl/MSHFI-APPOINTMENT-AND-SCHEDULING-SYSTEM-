<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['secretary', 'admin']);

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    if (validateCsrfToken($_POST['_csrf_token'] ?? '')) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'confirmed' WHERE id = ? AND status = 'pending'");
        $stmt->execute([$id]);
        $_SESSION['flash'] = ['message' => 'Appointment confirmed successfully.', 'type' => 'success'];
    }
}

$redirect = 'dashboard.php';
if (!empty($_SERVER['HTTP_REFERER']) && str_contains($_SERVER['HTTP_REFERER'], 'appointments.php')) {
    $redirect = 'appointments.php';
}
header("Location: $redirect");
exit;
