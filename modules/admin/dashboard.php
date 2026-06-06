<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

require_once __DIR__ . '/../../config/database.php';

// Count stats
$totalDoctors = $pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
$totalPatients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$todayAppts = $pdo->query("SELECT COUNT(*) FROM appointments WHERE appointment_date = CURDATE()")->fetchColumn();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-shield-lock"></i> Admin Dashboard</h3>
    <a href="manage_users.php" class="btn btn-primary"><i class="bi bi-people"></i> Manage Users</a>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-person-badge text-primary" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $totalDoctors ?></h4>
                <p class="text-muted mb-0">Doctors</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-people text-success" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $totalPatients ?></h4>
                <p class="text-muted mb-0">Patients</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-people-fill text-info" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $totalUsers ?></h4>
                <p class="text-muted mb-0">Total Users</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-day text-warning" style="font-size: 2rem;"></i>
                <h4 class="mt-2"><?= $todayAppts ?></h4>
                <p class="text-muted mb-0">Today's Appointments</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-clock"></i> Quick Links</h5></div>
            <div class="card-body">
                <div class="list-group">
                    <a href="manage_users.php" class="list-group-item list-group-item-action"><i class="bi bi-people"></i> Manage Users (Doctors, Staff, Patients)</a>
                    <a href="../secretary/appointments.php" class="list-group-item list-group-item-action"><i class="bi bi-calendar-event"></i> View All Appointments</a>
                    <a href="add_doctor.php" class="list-group-item list-group-item-action"><i class="bi bi-person-plus"></i> Add New Doctor</a>
                    <a href="add_staff.php" class="list-group-item list-group-item-action"><i class="bi bi-person-plus"></i> Add New Staff</a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header"><h5 class="mb-0"><i class="bi bi-info-circle"></i> System Info</h5></div>
            <div class="card-body">
                <p><strong>System:</strong> Web-Based Medical Appointment and Scheduling System</p>
                <p><strong>Hospital:</strong> Mindalano Specialist Hospital Foundation Inc.</p>
                <p><strong>Location:</strong> Lanao del Sur, Philippines</p>
                <p><strong>PHP Version:</strong> <?= phpversion() ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
