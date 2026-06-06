<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/messages.php';
require_once __DIR__ . '/../../includes/images.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();

if (!$doctor) {
    die('Doctor profile not found. Contact admin.');
}

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

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND status = 'completed'");
$stmt->execute([$doctor['id']]);
$completedCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE() AND status != 'cancelled'");
$stmt->execute([$doctor['id']]);
$todayCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE() AND status = 'pending'");
$stmt->execute([$doctor['id']]);
$pendingToday = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM patients p JOIN appointments a ON p.id = a.patient_id WHERE a.doctor_id = ?");
$stmt->execute([$doctor['id']]);
$totalPatients = (int)$stmt->fetchColumn();

$unreadMessages = getUnreadMessageCount($pdo, $userId);
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h3><i class="bi bi-person-badge text-primary me-2"></i>Doctor Dashboard</h3>
            <p class="text-muted mb-0">Dr. <?= htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']) ?> — <?= htmlspecialchars($doctor['specialization'] ?? 'General') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="schedule.php" class="btn btn-outline-primary"><i class="bi bi-calendar-week"></i> Schedule</a>
            <a href="patients.php" class="btn btn-outline-primary"><i class="bi bi-people"></i> Patients</a>
            <a href="messages.php" class="btn btn-outline-primary position-relative">
                <i class="bi bi-chat-dots"></i>
                <?php if ($unreadMessages > 0): ?>
                    <span class="badge bg-danger" style="font-size: 0.6rem; position: absolute; top: 2px; right: 2px;"><?= $unreadMessages ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-primary text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-calendar-day"></i>
            </div>
            <div class="stat-number"><?= $todayCount ?></div>
            <div class="stat-label">Today's Appointments</div>
            <i class="bi bi-calendar-day stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-warning text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-number"><?= $pendingToday ?></div>
            <div class="stat-label">Pending Today</div>
            <i class="bi bi-clock stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-success text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-check-all"></i>
            </div>
            <div class="stat-number"><?= $completedCount ?></div>
            <div class="stat-label">Completed</div>
            <i class="bi bi-check-all stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-info text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-number"><?= $totalPatients ?></div>
            <div class="stat-label">Total Patients</div>
            <i class="bi bi-people stat-bg-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Upcoming Appointments -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list me-2"></i>Upcoming Appointments</span>
                <span class="badge bg-primary"><?= count($upcoming) ?> upcoming</span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($upcoming)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-check text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No upcoming appointments.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Patient</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
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
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($appt['patient_first'] . ' ' . $appt['patient_last']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($appt['contact'] ?? '') ?></small>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                                    <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : 'warning' ?>"><?= ucfirst($appt['status']) ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end">
                                            <a href="patient_images.php?appointment_id=<?= $appt['id'] ?>" class="btn btn-sm <?= $hasImages ? 'btn-info' : 'btn-outline-secondary' ?>" title="Images">
                                                <i class="bi bi-images"></i>
                                            </a>
                                            <a href="messages.php?user=<?= $patUserId ?>" class="btn btn-sm btn-outline-primary" title="Message">
                                                <i class="bi bi-chat"></i>
                                            </a>
                                            <a href="record.php?appointment_id=<?= $appt['id'] ?>" class="btn btn-sm btn-success" title="Medical Record">
                                                <i class="bi bi-file-medical"></i>
                                            </a>
                                            <a href="complete.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-success" data-confirm="Mark this appointment as completed?" title="Complete">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
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
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Actions</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="schedule.php" class="action-card">
                            <div class="action-icon" style="background: var(--primary-100); color: var(--primary-dark);">
                                <i class="bi bi-calendar-week"></i>
                            </div>
                            <div class="action-title">Schedule</div>
                            <div class="action-desc">Manage slots</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="patients.php" class="action-card">
                            <div class="action-icon" style="background: #dbeafe; color: #1d4ed8;">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="action-title">Patients</div>
                            <div class="action-desc">View all</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="messages.php" class="action-card">
                            <div class="action-icon" style="background: #d1fae5; color: #065f46;">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <div class="action-title">Messages</div>
                            <div class="action-desc"><?= $unreadMessages ?> unread</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="notifications.php" class="action-card">
                            <div class="action-icon" style="background: #fef3c7; color: #92400e;">
                                <i class="bi bi-bell"></i>
                            </div>
                            <div class="action-title">Notifications</div>
                            <div class="action-desc">View all</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Overview -->
        <div class="card">
            <div class="card-header"><i class="bi bi-pie-chart me-2"></i>Today's Overview</div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span>Total Appointments</span>
                    <span class="fw-bold"><?= $todayCount ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span>Pending</span>
                    <span class="fw-bold text-warning"><?= $pendingToday ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span>Completed Today</span>
                    <span class="fw-bold text-success"><?= max(0, $todayCount - $pendingToday) ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-0">
                    <span>Unread Messages</span>
                    <span class="fw-bold text-info"><?= $unreadMessages ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
