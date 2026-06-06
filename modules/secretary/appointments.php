<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('secretary');

require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->prepare("
    SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
");
$stmt->execute();
$appointments = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-calendar-event"></i> All Appointments</h4>
    <a href="dashboard.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?= htmlspecialchars($appt['p_first'] . ' ' . $appt['p_last']) ?></td>
                        <td>Dr. <?= htmlspecialchars($appt['d_first'] . ' ' . $appt['d_last']) ?></td>
                        <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                <?= ucfirst($appt['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($appt['status'] === 'pending'): ?>
                                <a href="confirm.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-primary">Confirm</a>
                            <?php endif; ?>
                            <?php if ($appt['status'] !== 'cancelled' && $appt['status'] !== 'completed'): ?>
                                <a href="cancel.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel?')">Cancel</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
