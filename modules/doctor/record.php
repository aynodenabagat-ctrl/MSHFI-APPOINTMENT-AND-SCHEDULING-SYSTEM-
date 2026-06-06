<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();
if (!$doctor) { die('Doctor profile not found.'); }

$appointmentId = (int)($_GET['appointment_id'] ?? 0);
$patientId = (int)($_GET['patient_id'] ?? 0);

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diag = trim($_POST['diagnosis'] ?? '');
    $rx = trim($_POST['prescription'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $apptId = (int)($_POST['appointment_id'] ?? 0);
    $patId = (int)($_POST['patient_id'] ?? 0);

    if ($patId) {
        $stmt = $pdo->prepare("INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, prescription, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$patId, $doctor['id'], $apptId ?: null, $diag, $rx, $notes]);

        // Mark appointment as completed if linked
        if ($apptId) {
            $stmt = $pdo->prepare("UPDATE appointments SET status = 'completed' WHERE id = ? AND doctor_id = ?");
            $stmt->execute([$apptId, $doctor['id']]);
        }
        $success = 'Medical record saved successfully!';
    } else {
        $error = 'Patient not specified.';
    }
}

// Load appointment info if appointment_id given
$appointment = null;
if ($appointmentId) {
    $stmt = $pdo->prepare("
        SELECT a.*, p.first_name AS p_first, p.last_name AS p_last
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        WHERE a.id = ? AND a.doctor_id = ?
    ");
    $stmt->execute([$appointmentId, $doctor['id']]);
    $appointment = $stmt->fetch();
    if ($appointment) $patientId = $appointment['patient_id'];
}

// Load patient info
$patient = null;
if ($patientId) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();
}

// Get previous records for this patient
$records = [];
if ($patientId) {
    $stmt = $pdo->prepare("SELECT mr.*, a.appointment_date FROM medical_records mr LEFT JOIN appointments a ON mr.appointment_id = a.id WHERE mr.patient_id = ? AND mr.doctor_id = ? ORDER BY mr.created_at DESC");
    $stmt->execute([$patientId, $doctor['id']]);
    $records = $stmt->fetchAll();
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3">
                    <i class="bi bi-file-medical"></i>
                    <?php if ($appointment): ?>
                        Medical Record for <?= htmlspecialchars($appointment['p_first'] . ' ' . $appointment['p_last']) ?>
                        <small class="text-muted">(<?= date('M d, Y', strtotime($appointment['appointment_date'])) ?>)</small>
                    <?php elseif ($patient): ?>
                        Medical Record for <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                    <?php else: ?>
                        New Medical Record
                    <?php endif; ?>
                </h5>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <input type="hidden" name="appointment_id" value="<?= $appointmentId ?>">
                    <input type="hidden" name="patient_id" value="<?= $patientId ?>">
                    <div class="mb-3">
                        <label class="form-label">Diagnosis</label>
                        <textarea name="diagnosis" class="form-control" rows="3" placeholder="Enter diagnosis..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prescription</label>
                        <textarea name="prescription" class="form-control" rows="3" placeholder="Enter prescribed medications..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Additional Notes</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Other notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Save Record</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-clock-history"></i> Previous Records</h5>
                <?php if (empty($records)): ?>
                    <p class="text-muted mb-0">No previous records.</p>
                <?php else: ?>
                    <?php foreach ($records as $r): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <small class="text-muted"><?= date('M d, Y h:i A', strtotime($r['created_at'])) ?></small>
                        <?php if ($r['diagnosis']): ?><p class="mb-0"><strong>Diagnosis:</strong> <?= htmlspecialchars($r['diagnosis']) ?></p><?php endif; ?>
                        <?php if ($r['prescription']): ?><p class="mb-0"><strong>Prescription:</strong> <?= htmlspecialchars($r['prescription']) ?></p><?php endif; ?>
                        <?php if ($r['notes']): ?><p class="mb-0 text-muted"><?= htmlspecialchars($r['notes']) ?></p><?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
