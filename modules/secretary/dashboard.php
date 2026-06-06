<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('secretary');

require_once __DIR__ . '/../../config/database.php';

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
$pendingCount = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM patients");
$patientCount = (int)$stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM doctors");
$doctorCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE() AND status = 'confirmed'");
$stmt->execute();
$confirmedToday = (int)$stmt->fetchColumn();

$totalAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h3><i class="bi bi-person-lines-fill text-primary me-2"></i>Secretary Dashboard</h3>
            <p class="text-muted mb-0">Manage appointments and patient registrations</p>
        </div>
        <a href="register_patient.php" class="btn btn-success"><i class="bi bi-person-plus"></i> Register Patient</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-primary text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-number"><?= $patientCount ?></div>
            <div class="stat-label">Total Patients</div>
            <i class="bi bi-people stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-info text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-number"><?= $doctorCount ?></div>
            <div class="stat-label">Doctors</div>
            <i class="bi bi-person-badge stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-success text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-number"><?= count($todayAppts) ?></div>
            <div class="stat-label">Today's Appointments</div>
            <i class="bi bi-calendar-check stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-warning text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-number"><?= $pendingCount ?></div>
            <div class="stat-label">Pending Today</div>
            <i class="bi bi-clock stat-bg-icon"></i>
        </div>
    </div>
</div>

<!-- Secondary stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="card text-center py-3">
            <div class="fw-bold text-info" style="font-size: 1.3rem;"><?= $confirmedToday ?></div>
            <div class="text-muted small">Confirmed Today</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-center py-3">
            <div class="fw-bold text-primary" style="font-size: 1.3rem;"><?= $totalAppointments ?></div>
            <div class="text-muted small">All Time</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-list me-2"></i>Today's Queue</span>
        <a href="appointments.php" class="btn btn-sm btn-outline-primary">Manage All</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($todayAppts)): ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-check text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2 mb-0">No appointments for today.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($todayAppts as $i => $appt): ?>
                        <tr>
                            <td class="fw-semibold"><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($appt['p_first'] . ' ' . $appt['p_last']) ?></td>
                            <td>Dr. <?= htmlspecialchars($appt['d_first'] . ' ' . $appt['d_last']) ?></td>
                            <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                            <td>
                                <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                    <?= ucfirst($appt['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex gap-1 justify-content-end">
                                    <?php if ($appt['status'] === 'pending'): ?>
                                        <a href="confirm.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-check"></i> Confirm</a>
                                    <?php endif; ?>
                                    <?php if ($appt['status'] !== 'cancelled' && $appt['status'] !== 'completed'): ?>
                                        <a href="cancel.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Cancel this appointment?"><i class="bi bi-x"></i> Cancel</a>
                                    <?php endif; ?>
                                </div>
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
