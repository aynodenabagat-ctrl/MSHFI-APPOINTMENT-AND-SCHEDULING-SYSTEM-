<?php require_once 'config/app.php'; ?>
<?php require_once 'includes/header.php'; ?>

<div class="hero-section text-center mb-5">
    <h1><i class="bi bi-hospital"></i> Mindalano Specialist Hospital</h1>
    <p class="lead fs-5 mt-3">Web-Based Medical Appointment and Scheduling System</p>
    <p class="fs-6">Book appointments, manage schedules, and access medical records online.</p>
    <?php if (!isset($_SESSION['user_id'])): ?>
        <div class="mt-4">
            <a href="login.php" class="btn btn-light btn-lg me-2"><i class="bi bi-box-arrow-in-right"></i> Login</a>
            <a href="register.php" class="btn btn-outline-light btn-lg"><i class="bi bi-person-plus"></i> Register</a>
        </div>
    <?php else: ?>
        <div class="mt-4">
            <a href="modules/<?= $_SESSION['user_role'] ?>/dashboard.php" class="btn btn-light btn-lg">
                <i class="bi bi-speedometer2"></i> Go to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <div class="card-body">
                <i class="bi bi-calendar-check text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3">Online Booking</h5>
                <p class="card-text text-muted">Book appointments with your preferred doctor anytime, anywhere.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <div class="card-body">
                <i class="bi bi-bell text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3">Email Notifications</h5>
                <p class="card-text text-muted">Receive confirmations, reminders, and cancellation alerts via email.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <div class="card-body">
                <i class="bi bi-file-medical text-primary" style="font-size: 3rem;"></i>
                <h5 class="card-title mt-3">Digital Records</h5>
                <p class="card-text text-muted">Secure access to your medical history and consultation records.</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
