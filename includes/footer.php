    </div>

<footer class="footer">
    <div class="container pt-5 pb-4">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="mb-3"><i class="bi bi-hospital text-primary-light me-2"></i>Mindalano Specialist Hospital</h5>
                <p>Providing quality healthcare services through innovative technology. Your health and well-being are our top priority.</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="#" class="social-link"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="social-link"><i class="bi bi-envelope"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><a href="<?= BASE_URL ?>/index.php">Home</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/login.php">Login</a></li>
                    <li class="mb-2"><a href="<?= BASE_URL ?>/register.php">Register</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Contact Info</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Lanao del Sur, Philippines</li>
                    <li class="mb-2"><i class="bi bi-telephone me-2"></i>(063) 123-4567</li>
                    <li class="mb-2"><i class="bi bi-envelope me-2"></i>info@mindalanohospital.com</li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-4">
                <h5>Clinic Hours</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2 d-flex justify-content-between">
                        <span>Mon - Fri</span>
                        <span>8:00 AM - 5:00 PM</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span>Saturday</span>
                        <span>8:00 AM - 12:00 PM</span>
                    </li>
                    <li class="mb-2 d-flex justify-content-between">
                        <span>Sunday</span>
                        <span>Closed</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <span>&copy; <?= date('Y') ?> Mindalano Specialist Hospital Foundation Inc. All rights reserved.</span>
            <span class="text-muted">Web-Based Medical Appointment and Scheduling System</span>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/script.js"></script>
</body>
</html>
