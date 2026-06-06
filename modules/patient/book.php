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

// Get doctors
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

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="mb-4"><i class="bi bi-plus-circle"></i> Book an Appointment</h3>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Select Doctor</label>
                        <select name="doctor_id" class="form-select form-select-lg" required onchange="loadSchedule(this.value)">
                            <option value="">-- Choose Doctor --</option>
                            <?php foreach ($doctors as $doc): ?>
                            <option value="<?= $doc['id'] ?>">Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?> (<?= htmlspecialchars($doc['specialization']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Appointment Date</label>
                        <input type="date" name="appointment_date" class="form-control form-control-lg" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Appointment Time</label>
                        <input type="time" name="appointment_time" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Any concerns or special requests..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-check-circle"></i> Book Appointment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
