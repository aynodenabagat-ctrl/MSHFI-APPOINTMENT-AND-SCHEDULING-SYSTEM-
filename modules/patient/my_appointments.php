<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('patient');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/images.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS doctor_first, d.last_name AS doctor_last, d.specialization 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = (SELECT id FROM patients WHERE user_id = ?)
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-list"></i> My Appointments</h3>
    <a href="book.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Appointment</a>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($appointments)): ?>
            <p class="text-muted text-center mb-0">No appointments found. <a href="book.php">Book your first appointment</a></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Doctor</th>
                            <th>Specialization</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Images</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $i => $appt):
                            $imgCount = getImageCount($pdo, $appt['id']);
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>Dr. <?= htmlspecialchars($appt['doctor_first'] . ' ' . $appt['doctor_last']) ?></td>
                            <td><?= htmlspecialchars($appt['specialization']) ?></td>
                            <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                            <td><span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>"><?= ucfirst($appt['status']) ?></span></td>
                            <td>
                                <a href="upload_images.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-info <?= $appt['status'] === 'cancelled' ? 'disabled' : '' ?>">
                                    <i class="bi bi-images"></i> <?= $imgCount ?>/<?= MAX_IMAGES_PER_APPOINTMENT ?>
                                </a>
                            </td>
                            <td>
                                <?php if ($appt['status'] !== 'cancelled'): ?>
                                    <a href="messages.php?user=<?= $appt['doctor_id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-chat"></i></a>
                                <?php endif; ?>
                                <?php if ($appt['status'] === 'pending' || $appt['status'] === 'confirmed'): ?>
                                    <a href="cancel.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this appointment?')"><i class="bi bi-x-circle"></i></a>
                                <?php else: ?>
                                    <span class="text-muted">--</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
