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
    SELECT a.*, d.first_name AS doctor_first, d.last_name AS doctor_last, d.specialization
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = (SELECT id FROM patients WHERE user_id = ?)
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$appointments = $stmt->fetchAll();

// Count stats
$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = (SELECT id FROM patients WHERE user_id = ?)");
$stmt->execute([$userId]);
$totalAppointments = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = (SELECT id FROM patients WHERE user_id = ?) AND status = 'pending'");
$stmt->execute([$userId]);
$pendingCount = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE patient_id = (SELECT id FROM patients WHERE user_id = ?) AND status = 'completed'");
$stmt->execute([$userId]);
$completedCount = (int)$stmt->fetchColumn();

$unreadMessages = getUnreadMessageCount($pdo, $userId);
$upcomingAppt = null;
$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS doctor_first, d.last_name AS doctor_last
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = (SELECT id FROM patients WHERE user_id = ?)
    AND a.appointment_date >= CURDATE() AND a.status IN ('pending', 'confirmed')
    ORDER BY a.appointment_date ASC, a.appointment_time ASC
    LIMIT 1
");
$stmt->execute([$userId]);
$upcomingAppt = $stmt->fetch();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h3><i class="bi bi-person-circle text-primary me-2"></i>Patient Dashboard</h3>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Patient') ?>!</p>
        </div>
        <div class="d-flex gap-2">
            <a href="book.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Book Appointment</a>
            <a href="messages.php" class="btn btn-outline-primary"><i class="bi bi-chat-dots"></i></a>
        </div>
    </div>
</div>

<?php if (!$patient): ?>
<div class="alert alert-warning alert-permanent">
    <i class="bi bi-exclamation-triangle"></i> Please complete your profile to start booking appointments.
    <a href="profile.php" class="alert-link fw-semibold">Complete Profile</a>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-primary text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-number"><?= $totalAppointments ?></div>
            <div class="stat-label">Total Appointments</div>
            <i class="bi bi-calendar-check stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-warning text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-clock"></i>
            </div>
            <div class="stat-number"><?= $pendingCount ?></div>
            <div class="stat-label">Pending</div>
            <i class="bi bi-clock stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-success text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="stat-number"><?= $completedCount ?></div>
            <div class="stat-label">Completed</div>
            <i class="bi bi-check-circle stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-info text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-chat-dots"></i>
            </div>
            <div class="stat-number"><?= $unreadMessages ?></div>
            <div class="stat-label">Unread Messages</div>
            <i class="bi bi-chat-dots stat-bg-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Upcoming Appointment -->
        <?php if ($upcomingAppt): ?>
        <div class="card mb-4 border-0" style="background: linear-gradient(135deg, var(--primary-50), #fff);">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <small class="text-muted text-uppercase fw-semibold tracking-wide">Next Appointment</small>
                    <h5 class="fw-bold mb-1">
                        Dr. <?= htmlspecialchars($upcomingAppt['doctor_first'] . ' ' . $upcomingAppt['doctor_last']) ?>
                    </h5>
                    <div class="d-flex gap-3 text-muted small">
                        <span><i class="bi bi-calendar me-1"></i><?= date('M d, Y', strtotime($upcomingAppt['appointment_date'])) ?></span>
                        <span><i class="bi bi-clock me-1"></i><?= date('h:i A', strtotime($upcomingAppt['appointment_time'])) ?></span>
                    </div>
                </div>
                <span class="badge bg-<?= $upcomingAppt['status'] === 'confirmed' ? 'primary' : 'warning' ?> fs-6 px-3 py-2"><?= ucfirst($upcomingAppt['status']) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Appointments -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list me-2"></i>Recent Appointments</span>
                <a href="my_appointments.php" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($appointments)): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 mb-0">No appointments yet.</p>
                        <a href="book.php" class="btn btn-primary mt-2 btn-sm">Book one now!</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Doctor</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appt): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold">Dr. <?= htmlspecialchars($appt['doctor_first'] . ' ' . $appt['doctor_last']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($appt['specialization'] ?? '') ?></small>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                                    <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                            <?= ucfirst($appt['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if ($appt['status'] === 'pending' || $appt['status'] === 'confirmed'): ?>
                                            <a href="cancel.php?id=<?= $appt['id'] ?>" class="btn btn-sm btn-outline-danger" data-confirm="Cancel this appointment?">
                                                <i class="bi bi-x"></i> Cancel
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">--</span>
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
    </div>

    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Actions</div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-6">
                        <a href="book.php" class="action-card">
                            <div class="action-icon" style="background: var(--primary-100); color: var(--primary-dark);">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <div class="action-title">Book</div>
                            <div class="action-desc">New appointment</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="my_appointments.php" class="action-card">
                            <div class="action-icon" style="background: #dbeafe; color: #1d4ed8;">
                                <i class="bi bi-calendar-week"></i>
                            </div>
                            <div class="action-title">My Appointments</div>
                            <div class="action-desc">View all</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="messages.php" class="action-card">
                            <div class="action-icon" style="background: #d1fae5; color: #065f46;">
                                <i class="bi bi-chat-dots"></i>
                            </div>
                            <div class="action-title">Messages</div>
                            <div class="action-desc">Chat with doctor</div>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="profile.php" class="action-card">
                            <div class="action-icon" style="background: #fef3c7; color: #92400e;">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <div class="action-title">Profile</div>
                            <div class="action-desc">Update info</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Summary -->
        <?php if ($patient): ?>
        <div class="card">
            <div class="card-header"><i class="bi bi-person me-2"></i>Profile Summary</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="nav-avatar" style="width: 48px; height: 48px; font-size: 1.1rem;"><?= strtoupper(substr($patient['first_name'] ?? 'P', 0, 1) . substr($patient['last_name'] ?? 'U', 0, 1)) ?></span>
                    <div>
                        <div class="fw-semibold"><?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></div>
                        <small class="text-muted">Patient</small>
                    </div>
                </div>
                <?php if ($patient['contact']): ?>
                    <div class="d-flex justify-content-between py-1"><small class="text-muted">Contact</small><small><?= htmlspecialchars($patient['contact']) ?></small></div>
                <?php endif; ?>
                <?php if ($patient['blood_type']): ?>
                    <div class="d-flex justify-content-between py-1"><small class="text-muted">Blood Type</small><small class="fw-semibold"><?= htmlspecialchars($patient['blood_type']) ?></small></div>
                <?php endif; ?>
                <?php if ($patient['date_of_birth']): ?>
                    <div class="d-flex justify-content-between py-1"><small class="text-muted">DOB</small><small><?= date('M d, Y', strtotime($patient['date_of_birth'])) ?></small></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
