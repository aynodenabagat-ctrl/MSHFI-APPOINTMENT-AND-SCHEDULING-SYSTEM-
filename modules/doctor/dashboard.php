<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/messages.php';
require_once __DIR__ . '/../../includes/images.php';
$userId = getCurrentUserId();

// Get doctor info
$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    die('Doctor profile not found. Contact admin.');
}

// Get upcoming appointments
$stmt = $pdo->prepare("
    SELECT a.*, p.first_name AS patient_first, p.last_name AS patient_last, p.contact
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE() AND a.status IN ('pending', 'confirmed')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 10
");
$stmt->execute([$doctor['id']]);
$upcoming = $stmt->fetchAll();

// Count stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'completed'");
$stmt->execute([$doctor['id']]);
$completed = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE() AND status != 'cancelled'");
$stmt->execute([$doctor['id']]);
$todayCount = $stmt->fetchColumn();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-person-badge"></i> Doctor Dashboard</h3>
    <div>
        <a href="schedule.php" class="btn btn-outline-primary"><i class="bi bi-calendar-week"></i> My Schedule</a>
        <a href="patients.php" class="btn btn-outline-primary"><i class="bi bi-people"></i> My Patients</a>
        <a href="messages.php" class="btn btn-outline-primary"><i class="bi bi-chat-dots"></i> Messages</a>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-day text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= $todayCount ?></h3>
                <p class="text-muted mb-0">Today's Appointments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= count($upcoming) ?></h3>
                <p class="text-muted mb-0">Upcoming Appointments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-check-all text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= $completed ?></h3>
                <p class="text-muted mb-0">Completed</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-chat-dots text-info" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= getUnreadMessageCount($pdo, $userId) ?></h3>
                <p class="text-muted mb-0">Unread Messages</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-list"></i> Upcoming Appointments</h5>
    </div>
    <div class="card-body">
        <?php if (empty($upcoming)): ?>
            <p class="text-muted text-center mb-0">No upcoming appointments.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Contact</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Images</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($upcoming as $appt):
                            $hasImages = getImageCount($pdo, $appt['id']) > 0;
                            $stmt2 = $pdo->prepare("SELECT user_id FROM patients WHERE id = ?");
                            $stmt2->execute([$appt['patient_id']]);
                            $patUserId = $stmt2->fetchColumn();
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($appt['patient_first'] . ' ' . $appt['patient_last']) ?></td>
                            <td><?= htmlspecialchars($appt['contact'] ?? 'N/A') ?></td>
                            <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                            <td><span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : 'warning' ?>"><?= ucfirst($appt['status']) ?></span></td>
                            <td>
                                <a href="patient_images.php?appointment_id=<?= $appt['id'] ?>" class="btn btn-sm <?= $hasImages ? 'btn-info' : 'btn-outline-secondary' ?>">
                                    <i class="bi bi-images"></i>
                                </a>
                            </td>
                            <td>
                                <a href="messages.php?user=<?= $patUserId ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-chat"></i></a>
                                <a href="record.php?appointment_id=<?= $appt['id'] ?>" class="btn btn-sm btn-success"><i class="bi bi-file-medical"></i> Record</a>
                                <a href="complete.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Mark as completed?')"><i class="bi bi-check"></i></a>
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
