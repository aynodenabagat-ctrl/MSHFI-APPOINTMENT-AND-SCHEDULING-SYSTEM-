<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('secretary');

require_once __DIR__ . '/../../config/database.php';

// Today's appointments
$stmt = $pdo->prepare("
    SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.appointment_date = CURDATE()
    ORDER BY a.appointment_time ASC
");
$stmt->execute();
$todayAppts = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'pending'");
$stmt->execute();
$pendingCount = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM patients");
$stmt->execute();
$patientCount = $stmt->fetchColumn();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-person-lines-fill"></i> Secretary Dashboard</h3>
    <div>
        <a href="register_patient.php" class="btn btn-success btn-lg"><i class="bi bi-person-plus"></i> Register Patient</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= $patientCount ?></h3>
                <p class="text-muted mb-0">Total Patients</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= count($todayAppts) ?></h3>
                <p class="text-muted mb-0">Today's Appointments</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= $pendingCount ?></h3>
                <p class="text-muted mb-0">Pending Today</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list"></i> Today's Queue</h5>
        <a href="appointments.php" class="btn btn-sm btn-outline-primary">Manage All</a>
    </div>
    <div class="card-body">
        <?php if (empty($todayAppts)): ?>
            <p class="text-muted text-center mb-0">No appointments for today.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayAppts as $i => $appt): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($appt['p_first'] . ' ' . $appt['p_last']) ?></td>
                            <td>Dr. <?= htmlspecialchars($appt['d_first'] . ' ' . $appt['d_last']) ?></td>
                            <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                    <?= ucfirst($appt['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($appt['status'] === 'pending'): ?>
                                    <a href="confirm.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-check"></i> Confirm</a>
                                <?php endif; ?>
                                <?php if ($appt['status'] !== 'cancelled' && $appt['status'] !== 'completed'): ?>
                                    <a href="cancel.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Cancel this appointment?')"><i class="bi bi-x"></i> Cancel</a>
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
