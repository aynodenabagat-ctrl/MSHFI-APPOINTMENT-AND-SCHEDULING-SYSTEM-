<?php
require_once 'config/app.php';
require_once 'config/database.php';

// Get real stats
$totalDoctors = 0;
$totalPatients = 0;
$totalAppointments = 0;
$yearsOfService = 10;
try {
    $totalDoctors = (int)$pdo->query("SELECT COUNT(*) FROM doctors")->fetchColumn();
    $totalPatients = (int)$pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $totalAppointments = (int)$pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
} catch (Exception $e) {}

// Get doctors for featured section
$featuredDoctors = [];
try {
    $stmt = $pdo->query("SELECT d.*, u.email FROM doctors d JOIN users u ON d.user_id = u.id LIMIT 4");
    $featuredDoctors = $stmt->fetchAll();
} catch (Exception $e) {}
?>
<?php require_once 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-7 animate-fade-up">
                <span class="badge bg-white/10 text-white mb-3" style="background: rgba(255,255,255,0.12); backdrop-filter: blur(4px);">
                    <i class="bi bi-shield-check me-1"></i> Trusted Healthcare Provider
                </span>
                <h1>Your Health,<br>Our Commitment</h1>
                <p class="hero-subtitle mt-3">Book appointments with trusted specialists, manage your medical records, and experience healthcare made simple — all from the comfort of your home.</p>
                <div class="hero-cta mt-4">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn btn-light me-2"><i class="bi bi-person-plus"></i> Get Started</a>
                        <a href="login.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    <?php else: ?>
                        <a href="modules/<?= $_SESSION['user_role'] ?>/dashboard.php" class="btn btn-light btn-lg">
                            <i class="bi bi-speedometer2"></i> Go to Dashboard
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center animate-fade-up" style="animation-delay: 0.2s;">
                <div style="width: 340px; height: 340px; border-radius: 50%; background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.12);">
                    <i class="bi bi-heart-pulse" style="font-size: 8rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Bar -->
<div class="container">
    <div class="stats-bar">
        <div class="row">
            <div class="col-6 col-lg-3 stat-item">
                <div class="number"><span class="animate-counter" data-target="<?= $totalPatients ?>">0</span>+</div>
                <div class="label">Patients Served</div>
            </div>
            <div class="col-6 col-lg-3 stat-item">
                <div class="number"><span class="animate-counter" data-target="<?= $totalDoctors ?>">0</span>+</div>
                <div class="label">Expert Doctors</div>
            </div>
            <div class="col-6 col-lg-3 stat-item">
                <div class="number"><span class="animate-counter" data-target="<?= $totalAppointments ?>">0</span>+</div>
                <div class="label">Appointments Done</div>
            </div>
            <div class="col-6 col-lg-3 stat-item">
                <div class="number"><span class="animate-counter" data-target="<?= $yearsOfService ?>">0</span>+</div>
                <div class="label">Years of Service</div>
            </div>
        </div>
    </div>
</div>

<!-- Features -->
<section class="section">
    <div class="container">
        <div class="section-title reveal">
            <h2>Why Choose Mindalano Hospital?</h2>
            <p>We combine modern technology with compassionate care to give you the best healthcare experience.</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3 reveal">
                <div class="feature-card">
                    <div class="feature-icon" style="background: var(--primary-100); color: var(--primary-dark);">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h5>Online Booking</h5>
                    <p>Schedule appointments with your preferred doctor anytime, anywhere, with just a few clicks.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal" style="transition-delay: 0.1s;">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #dbeafe; color: #1d4ed8;">
                        <i class="bi bi-clock"></i>
                    </div>
                    <h5>Real-Time Scheduling</h5>
                    <p>View available time slots instantly and get immediate confirmation of your appointment.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal" style="transition-delay: 0.2s;">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #d1fae5; color: #065f46;">
                        <i class="bi bi-file-medical"></i>
                    </div>
                    <h5>Digital Records</h5>
                    <p>Securely access your medical history, prescriptions, and lab results online anytime.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 reveal" style="transition-delay: 0.3s;">
                <div class="feature-card">
                    <div class="feature-icon" style="background: #fef3c7; color: #92400e;">
                        <i class="bi bi-bell"></i>
                    </div>
                    <h5>Smart Notifications</h5>
                    <p>Receive timely reminders and updates about your appointments via email and in-app alerts.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section class="section" style="background: var(--gray-100);">
    <div class="container">
        <div class="section-title reveal">
            <h2>How It Works</h2>
            <p>Getting started is simple. Follow these steps to book your first appointment.</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-4 step-item reveal">
                <div class="step-number">1</div>
                <h5 class="fw-bold">Create an Account</h5>
                <p class="text-muted">Sign up as a patient and complete your profile with your basic information.</p>
            </div>
            <div class="col-md-4 step-item reveal" style="transition-delay: 0.15s;">
                <div class="step-number">2</div>
                <h5 class="fw-bold">Choose a Doctor</h5>
                <p class="text-muted">Browse our specialists, pick your preferred doctor, and select an available time slot.</p>
            </div>
            <div class="col-md-4 step-item reveal" style="transition-delay: 0.3s;">
                <div class="step-number">3</div>
                <h5 class="fw-bold">Visit & Get Care</h5>
                <p class="text-muted">Come to your appointment, receive quality care, and access your records online.</p>
            </div>
        </div>
    </div>
</section>

<!-- Doctors Preview -->
<?php if (!empty($featuredDoctors)): ?>
<section class="section">
    <div class="container">
        <div class="section-title reveal">
            <h2>Meet Our Specialists</h2>
            <p>Our team of experienced doctors is here to provide you with the best medical care.</p>
        </div>
        <div class="row g-4">
            <?php foreach ($featuredDoctors as $doc): ?>
            <div class="col-md-6 col-lg-3 reveal">
                <div class="card text-center h-100 border-0" style="border-radius: var(--radius);">
                    <div class="card-body p-4">
                        <div style="width: 80px; height: 80px; border-radius: 50%; background: var(--primary-100); display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                            <i class="bi bi-person-badge" style="font-size: 2rem; color: var(--primary-dark);"></i>
                        </div>
                        <h6 class="fw-bold mb-1">Dr. <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></h6>
                        <p class="text-muted small mb-0"><?= htmlspecialchars($doc['specialization'] ?? 'General Practice') ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="section" style="background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; text-align: center;">
    <div class="container reveal">
        <h2 class="text-white fw-bold mb-3">Ready to Book Your Appointment?</h2>
        <p class="mb-4" style="opacity: 0.9; font-size: 1.1rem;">Join thousands of patients who trust Mindalano Specialist Hospital for their healthcare needs.</p>
        <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="register.php" class="btn btn-light btn-lg px-5"><i class="bi bi-person-plus"></i> Get Started Now</a>
        <?php else: ?>
            <a href="modules/<?= $_SESSION['user_role'] ?>/dashboard.php" class="btn btn-light btn-lg px-5"><i class="bi bi-speedometer2"></i> Go to Dashboard</a>
        <?php endif; ?>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
