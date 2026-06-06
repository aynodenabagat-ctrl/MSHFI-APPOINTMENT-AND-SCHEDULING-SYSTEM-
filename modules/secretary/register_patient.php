<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('secretary');

require_once __DIR__ . '/../../config/database.php';

$success = '';
$error = '';
$username = $email = $password = $firstName = $lastName = $contact = $address = $dob = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = $_POST['date_of_birth'] ?? '';

    if ($username && $email && $password && $firstName && $lastName) {
        if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $check->execute([$email, $username]);
        if ($check->fetch()) {
            $error = 'Email or username already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, first_name, last_name, role) VALUES (?, ?, ?, ?, ?, 'patient')");
            $stmt->execute([$username, $email, $hash, $firstName, $lastName]);
            $userId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO patients (user_id, first_name, last_name, contact, address, date_of_birth) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $firstName, $lastName, $contact, $address, $dob]);
            $success = 'Patient registered successfully!';
            $username = $email = $password = $firstName = $lastName = $contact = $address = $dob = '';
        }
        }
    } else {
        $error = 'Please fill in all required fields.';
    }
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body p-4">
                <h4 class="mb-4"><i class="bi bi-person-plus"></i> Register New Patient</h4>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username <span class="text-danger">*</span></label>
                            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($firstName) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($lastName) ?>" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact" class="form-control" value="<?= htmlspecialchars($contact) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($dob) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($address) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="bi bi-person-plus"></i> Register Patient</button>
                </form>
                <div class="mt-3">
                    <a href="dashboard.php" class="btn btn-outline-secondary w-100"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
