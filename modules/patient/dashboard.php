<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('patient');

$userId = getCurrentUserId();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/messages.php';
require_once __DIR__ . '/../../includes/images.php';

$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$userId]);
$patient = $stmt->fetch();

$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS doctor_first, d.last_name AS doctor_last 
    FROM appointments a 
    JOIN doctors d ON a.doctor_id = d.id 
    WHERE a.patient_id = (SELECT id FROM patients WHERE user_id = ?)
    ORDER BY a.appointment_date DESC, a.appointment_time DESC 
    LIMIT 5
");
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-person-circle"></i> Patient Dashboard</h2>
    <a href="book.php" class="btn btn-primary btn-lg"><i class="bi bi-plus-circle"></i> Book Appointment</a>
</div>

<?php if (!$patient): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle"></i> Please complete your profile to start booking appointments.
    <a href="profile.php" class="alert-link">Complete Profile</a>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check text-primary" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= count($appointments) ?></h3>
                <p class="text-muted mb-0">Total Appointments</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-clock text-warning" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'pending')) ?></h3>
                <p class="text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                <h3 class="mt-2"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'completed')) ?></h3>
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

<div class="mb-4">
    <a href="messages.php" class="btn btn-outline-primary me-2"><i class="bi bi-chat-dots"></i> My Messages</a>
    <a href="my_appointments.php" class="btn btn-outline-info"><i class="bi bi-images"></i> My Images</a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-list"></i> Recent Appointments</h5>
        <div>
            <a href="messages.php" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-chat-dots"></i> Messages</a>
            <a href="my_appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($appointments)): ?>
            <p class="text-muted text-center mb-0">No appointments yet. <a href="book.php">Book one now!</a></p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td>Dr. <?= htmlspecialchars($appt['doctor_first'] . ' ' . $appt['doctor_last']) ?></td>
                            <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                            <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                            <td><span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>"><?= ucfirst($appt['status']) ?></span></td>
                            <td>
                                <?php if ($appt['status'] === 'pending' || $appt['status'] === 'confirmed'): ?>
                                    <a href="cancel.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this appointment?')">Cancel</a>
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
