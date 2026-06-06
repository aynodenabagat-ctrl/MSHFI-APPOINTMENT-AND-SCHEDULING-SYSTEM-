<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/images.php';

$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();
if (!$doctor) { die('Doctor profile not found.'); }

$appointmentId = (int)($_GET['appointment_id'] ?? 0);
$patientId = (int)($_GET['patient_id'] ?? 0);

$images = [];
$patient = null;
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
    if ($appointment) {
        $patientId = $appointment['patient_id'];
        $images = getAppointmentImages($pdo, $appointmentId);
    }
}

if ($patientId) {
    $stmt = $pdo->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->execute([$patientId]);
    $patient = $stmt->fetch();

    if (!$appointmentId) {
        $images = getPatientImages($pdo, $patientId, $doctor['id']);
    }
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-images"></i> Patient Images</h4>
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<?php if ($patient): ?>
<div class="card shadow mb-4">
    <div class="card-body">
        <h5>Patient: <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></h5>
        <?php if ($appointment): ?>
            <p class="text-muted mb-0">Appointment: <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?> at <?= date('h:i A', strtotime($appointment['appointment_time'])) ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body p-4">
        <?php if (empty($images)): ?>
            <p class="text-muted text-center mb-0">No images uploaded for this patient.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($images as $img): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card">
                        <a href="<?= BASE_URL ?>/uploads/appointments/<?= $img['file_name'] ?>" target="_blank">
                            <img src="<?= BASE_URL ?>/uploads/appointments/<?= $img['file_name'] ?>" class="card-img-top img-thumbnail" style="height:180px;object-fit:cover;" alt="<?= htmlspecialchars($img['original_name']) ?>">
                        </a>
                        <div class="card-body p-2">
                            <?php if ($img['caption']): ?>
                                <p class="small mb-1 text-muted"><?= htmlspecialchars($img['caption']) ?></p>
                            <?php endif; ?>
                            <small class="text-muted"><?= date('M d, Y', strtotime($img['uploaded_at'])) ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($patientId): ?>
<div class="mt-3">
    <a href="record.php?patient_id=<?= $patientId ?>" class="btn btn-primary"><i class="bi bi-file-medical"></i> Medical Records</a>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
