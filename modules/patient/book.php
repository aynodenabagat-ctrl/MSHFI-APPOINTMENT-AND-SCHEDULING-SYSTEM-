<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('patient');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/notifications.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmt->execute([$userId]);
$patient = $stmt->fetch();

if (!$patient) {
    header('Location: profile.php');
    exit;
}

$doctors = $pdo->query("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id")->fetchAll();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = (int)($_POST['doctor_id'] ?? 0);
    $apptDate = $_POST['appointment_date'] ?? '';
    $apptTime = $_POST['appointment_time'] ?? '';
    $notes = trim($_POST['notes'] ?? '');

    if ($doctorId && $apptDate && $apptTime) {
        $check = $pdo->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND appointment_time = ? AND status != 'cancelled'");
        $check->execute([$doctorId, $apptDate, $apptTime]);
        if ($check->fetch()) {
            $error = 'This time slot is already taken. Please choose another.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, notes) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$patient['id'], $doctorId, $apptDate, $apptTime, $notes]);
            createNotification($pdo, $userId, "Appointment booked for " . date('M d, Y', strtotime($apptDate)) . " at " . date('h:i A', strtotime($apptTime)));
            $success = 'Appointment booked successfully!';
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="dashboard-header">
    <h3><i class="bi bi-plus-circle text-primary me-2"></i>Book an Appointment</h3>
    <p class="text-muted">Fill in the details below to schedule your appointment with a specialist.</p>
</div>

<?php if ($success): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><?= $success ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><i class="bi bi-exclamation-circle-fill me-2"></i><?= $error ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-body p-4 p-lg-5">
                <form method="POST">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Select Doctor</label>
                        <select name="doctor_id" class="form-select form-select-lg" required>
                            <option value="">-- Choose a doctor --</option>
                            <?php foreach ($doctors as $doc): ?>
                            <option value="<?= $doc['id'] ?>">Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?> — <?= htmlspecialchars($doc['specialization'] ?? 'General') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Date</label>
                            <input type="date" name="appointment_date" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Appointment Time</label>
                            <input type="time" name="appointment_time" class="form-control form-control-lg" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Notes <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Any concerns, symptoms, or special requests..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-check-circle me-2"></i>Book Appointment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
