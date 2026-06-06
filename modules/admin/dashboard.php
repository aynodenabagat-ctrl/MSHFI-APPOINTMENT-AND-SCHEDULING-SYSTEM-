<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

require_once __DIR__ . '/../../config/database.php';

$totalDoctors = (int)$pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$todayAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
$totalAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$pendingAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
$completedAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'completed'")->fetchColumn();
$cancelledAppts = (int)$pdo->query("SELECT COUNT(*) FROM appointments WHERE status = 'cancelled'")->fetchColumn();

$stmt = $pdo->query("SELECT u.* FROM users u ORDER BY u.created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="dashboard-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
        <div>
            <h3><i class="bi bi-shield-lock text-primary me-2"></i>Admin Dashboard</h3>
            <p class="text-muted mb-0">System overview and management</p>
        </div>
        <a href="manage_users.php" class="btn btn-primary"><i class="bi bi-people"></i> Manage Users</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-primary text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-person-badge"></i>
            </div>
            <div class="stat-number"><?= $totalDoctors ?></div>
            <div class="stat-label">Doctors</div>
            <i class="bi bi-person-badge stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-success text-white">
            <div class="stat-icon" style="background: rgba(255,255,255,0.15);">
                <i class="bi bi-people"></i>
            </div>
            <div class="stat-number"><?= $totalPatients ?></div>
            <div class="stat-label">Patients</div>
            <i class="bi bi-people stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="stat-card bg-gradient-info text-white">
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
                <i class="bi bi-calendar-day"></i>
            </div>
            <div class="stat-number"><?= $todayAppts ?></div>
            <div class="stat-label">Today's Appointments</div>
            <i class="bi bi-calendar-day stat-bg-icon"></i>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row g-4 mb-4">
    <div class="col-lg-3 col-6">
        <div class="card text-center py-3">
            <div class="stat-number text-primary" style="font-size: 1.5rem;"><?= $totalUsers ?></div>
            <div class="text-muted small">Total Users</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card text-center py-3">
            <div class="stat-number text-warning" style="font-size: 1.5rem;"><?= $pendingAppts ?></div>
            <div class="text-muted small">Pending</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card text-center py-3">
            <div class="stat-number text-success" style="font-size: 1.5rem;"><?= $completedAppts ?></div>
            <div class="text-muted small">Completed</div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="card text-center py-3">
            <div class="stat-number text-danger" style="font-size: 1.5rem;"><?= $cancelledAppts ?></div>
            <div class="text-muted small">Cancelled</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-lightning me-2"></i>Quick Actions</div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="manage_users.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 px-0">
                        <span style="width: 40px; height: 40px; border-radius: 10px; background: var(--primary-100); display: flex; align-items: center; justify-content: center; color: var(--primary-dark);">
                            <i class="bi bi-people"></i>
                        </span>
                        <div><div class="fw-semibold">Manage Users</div><small class="text-muted">Doctors, Staff, Patients</small></div>
                    </a>
                    <a href="../secretary/appointments.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 px-0">
                        <span style="width: 40px; height: 40px; border-radius: 10px; background: #dbeafe; display: flex; align-items: center; justify-content: center; color: #1d4ed8;">
                            <i class="bi bi-calendar-event"></i>
                        </span>
                        <div><div class="fw-semibold">View Appointments</div><small class="text-muted">All appointments overview</small></div>
                    </a>
                    <a href="add_doctor.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 px-0">
                        <span style="width: 40px; height: 40px; border-radius: 10px; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #065f46;">
                            <i class="bi bi-person-plus"></i>
                        </span>
                        <div><div class="fw-semibold">Add New Doctor</div><small class="text-muted">Register a doctor account</small></div>
                    </a>
                    <a href="add_staff.php" class="list-group-item list-group-item-action d-flex align-items-center gap-3 border-0 px-0">
                        <span style="width: 40px; height: 40px; border-radius: 10px; background: #fef3c7; display: flex; align-items: center; justify-content: center; color: #92400e;">
                            <i class="bi bi-person-plus"></i>
                        </span>
                        <div><div class="fw-semibold">Add Staff</div><small class="text-muted">Register secretary staff</small></div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-people me-2"></i>Recent Users</div>
            <div class="card-body p-0">
                <?php if (empty($recentUsers)): ?>
                    <div class="text-center py-4 text-muted">No users yet.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $u): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?: 'N/A' ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small>
                                    </td>
                                    <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'doctor' ? 'primary' : ($u['role'] === 'secretary' ? 'info' : 'secondary')) ?>"><?= ucfirst($u['role']) ?></span></td>
                                    <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
