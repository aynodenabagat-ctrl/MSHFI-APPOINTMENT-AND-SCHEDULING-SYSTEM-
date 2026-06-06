<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('patient');

require_once __DIR__ . '/../../config/database.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
$stmt->execute([$userId]);
$patient = $stmt->fetch();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $dob = $_POST['date_of_birth'] ?? '';
    $bloodType = trim($_POST['blood_type'] ?? '');

    if ($firstName && $lastName) {
        // Sync name to users table
        $stmt = $pdo->prepare("UPDATE users SET first_name=?, last_name=? WHERE id=?");
        $stmt->execute([$firstName, $lastName, $userId]);

        if ($patient) {
            $stmt = $pdo->prepare("UPDATE patients SET first_name=?, last_name=?, contact=?, address=?, date_of_birth=?, blood_type=? WHERE user_id=?");
            $stmt->execute([$firstName, $lastName, $contact, $address, $dob, $bloodType, $userId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO patients (user_id, first_name, last_name, contact, address, date_of_birth, blood_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $firstName, $lastName, $contact, $address, $dob, $bloodType]);
        }

        $_SESSION['user_name'] = "$firstName $lastName";
        $success = 'Profile saved successfully!';
        $stmt = $pdo->prepare("SELECT * FROM patients WHERE user_id = ?");
        $stmt->execute([$userId]);
        $patient = $stmt->fetch();
    } else {
        $error = 'First name and last name are required.';
    }
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="mb-4"><i class="bi bi-person-vcard"></i> My Profile</h3>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="first_name" class="form-control form-control-lg" value="<?= htmlspecialchars($patient['first_name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="last_name" class="form-control form-control-lg" value="<?= htmlspecialchars($patient['last_name'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact" class="form-control form-control-lg" value="<?= htmlspecialchars($patient['contact'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($patient['address'] ?? '') ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control form-control-lg" value="<?= $patient['date_of_birth'] ?? '' ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Blood Type</label>
                            <select name="blood_type" class="form-select form-select-lg">
                                <option value="">-- Select --</option>
                                <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bt): ?>
                                <option value="<?= $bt ?>" <?= ($patient['blood_type'] ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100"><i class="bi bi-save"></i> Save Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
