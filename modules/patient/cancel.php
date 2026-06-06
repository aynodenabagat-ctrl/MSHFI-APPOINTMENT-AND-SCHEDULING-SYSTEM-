<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('patient');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/notifications.php';
$appointmentId = (int)($_GET['id'] ?? 0);

if ($appointmentId) {
    $userId = getCurrentUserId();
    $stmt = $pdo->prepare("
        SELECT a.* FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.id = ? AND p.user_id = ? AND a.status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$appointmentId, $userId]);
    $appt = $stmt->fetch();

    if ($appt) {
        $stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$appointmentId]);
        createNotification($pdo, $userId, "Appointment on " . date('M d, Y', strtotime($appt['appointment_date'])) . " has been cancelled.");
    }
}

header('Location: my_appointments.php');
exit;
