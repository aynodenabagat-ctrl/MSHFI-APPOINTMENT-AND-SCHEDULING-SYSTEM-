<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('patient');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/images.php';
require_once __DIR__ . '/../../includes/notifications.php';

$userId = getCurrentUserId();
$appointmentId = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare("
    SELECT a.*, d.first_name AS d_first, d.last_name AS d_last
    FROM appointments a
    JOIN doctors d ON a.doctor_id = d.id
    JOIN patients p ON a.patient_id = p.id
    WHERE a.id = ? AND p.user_id = ? AND a.status NOT IN ('cancelled')
");
$stmt->execute([$appointmentId, $userId]);
$appointment = $stmt->fetch();

if (!$appointment) {
    header('Location: my_appointments.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id FROM patients WHERE user_id = ?");
$stmt->execute([$userId]);
$patient = $stmt->fetch();
$patientId = $patient['id'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_image'])) {
        deleteAppointmentImage($pdo, (int)$_POST['delete_image'], $patientId);
        $success = 'Image removed.';
    } elseif (!empty($_FILES['images']['name'][0])) {
        $caption = trim($_POST['caption'] ?? '');
        $files = $_FILES['images'];
        $count = count(array_filter($files['name']));
        $current = getImageCount($pdo, $appointmentId);
        $remaining = MAX_IMAGES_PER_APPOINTMENT - $current;

        if ($count > $remaining) {
            $error = "You can only upload $remaining more image(s). Max is " . MAX_IMAGES_PER_APPOINTMENT . ".";
        } else {
            for ($i = 0; $i < $count; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                $err = uploadAppointmentImage($pdo, $appointmentId, $patientId, $file, $caption);
                if ($err) { $error = $err; break; }
            }
            if (!$error) {
                $success = 'Image(s) uploaded successfully.';
                createNotification($pdo, $userId, "Images uploaded for appointment on " . date('M d, Y', strtotime($appointment['appointment_date'])));
            }
        }
    }
}

$images = getAppointmentImages($pdo, $appointmentId);
$imgCount = count($images);
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-images"></i> Manage Images</h4>
    <a href="my_appointments.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <h5>Appointment: Dr. <?= htmlspecialchars($appointment['d_first'] . ' ' . $appointment['d_last']) ?> &mdash; <?= date('M d, Y', strtotime($appointment['appointment_date'])) ?> at <?= date('h:i A', strtotime($appointment['appointment_time'])) ?></h5>
        <p class="text-muted mb-0">Images: <?= $imgCount ?> / <?= MAX_IMAGES_PER_APPOINTMENT ?></p>
    </div>
</div>

<?php if ($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>

<?php if ($imgCount < MAX_IMAGES_PER_APPOINTMENT): ?>
<div class="card shadow mb-4">
    <div class="card-body p-4">
        <h5><i class="bi bi-upload"></i> Upload Images</h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Select images (max <?= MAX_IMAGES_PER_APPOINTMENT - $imgCount ?> files, 5MB each)</label>
                <input type="file" name="images[]" class="form-control" multiple accept="image/*">
            </div>
            <div class="mb-3">
                <label class="form-label">Caption (optional)</label>
                <input type="text" name="caption" class="form-control" placeholder="Brief description...">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-upload"></i> Upload</button>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body p-4">
        <h5><i class="bi bi-images"></i> Uploaded Images</h5>
        <?php if (empty($images)): ?>
            <p class="text-muted text-center mb-0">No images uploaded yet.</p>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($images as $img): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card">
                        <a href="<?= BASE_URL ?>/uploads/appointments/<?= $img['file_name'] ?>" target="_blank">
                            <img src="<?= BASE_URL ?>/uploads/appointments/<?= $img['file_name'] ?>" class="card-img-top img-thumbnail" style="height:150px;object-fit:cover;" alt="<?= htmlspecialchars($img['original_name']) ?>">
                        </a>
                        <div class="card-body p-2">
                            <?php if ($img['caption']): ?>
                                <p class="small mb-1 text-muted"><?= htmlspecialchars($img['caption']) ?></p>
                            <?php endif; ?>
                            <form method="POST" style="display:inline">
                                <button type="submit" name="delete_image" value="<?= $img['id'] ?>" class="btn btn-sm btn-danger w-100" onclick="return confirm('Delete this image?')"><i class="bi bi-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
