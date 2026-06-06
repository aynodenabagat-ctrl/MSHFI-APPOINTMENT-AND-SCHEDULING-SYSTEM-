<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();
if (!$doctor) { die('Doctor profile not found.'); }

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $pdo->prepare("DELETE FROM doctor_schedules WHERE id = ? AND doctor_id = ?");
        $stmt->execute([(int)$_POST['delete'], $doctor['id']]);
        $success = 'Schedule slot removed.';
    } elseif (isset($_POST['day_of_week'])) {
        $day = (int)$_POST['day_of_week'];
        $start = $_POST['start_time'] ?? '';
        $end = $_POST['end_time'] ?? '';

        if ($day >= 0 && $day <= 6 && $start && $end) {
            $stmt = $pdo->prepare("INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
            $stmt->execute([$doctor['id'], $day, $start, $end]);
            $success = 'Schedule added successfully.';
        } else {
            $error = 'Please fill in all fields correctly.';
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM doctor_schedules WHERE doctor_id = ? ORDER BY day_of_week, start_time");
$stmt->execute([$doctor['id']]);
$schedules = $stmt->fetchAll();

$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="row">
    <div class="col-md-5">
        <div class="card shadow mb-4">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-plus-circle"></i> Add Schedule Slot</h5>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Day of Week</label>
                        <select name="day_of_week" class="form-select" required>
                            <?php foreach ($days as $i => $d): ?>
                            <option value="<?= $i ?>"><?= $d ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Time</label>
                            <input type="time" name="start_time" class="form-control" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Time</label>
                            <input type="time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> Add Slot</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow">
            <div class="card-body p-4">
                <h5 class="mb-3"><i class="bi bi-calendar-week"></i> Current Schedule</h5>
                <?php if (empty($schedules)): ?>
                    <p class="text-muted mb-0">No schedules set. Add your available slots.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Day</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $s): ?>
                                <tr>
                                    <td><?= $days[$s['day_of_week']] ?></td>
                                    <td><?= date('h:i A', strtotime($s['start_time'])) ?></td>
                                    <td><?= date('h:i A', strtotime($s['end_time'])) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline">
                                            <button type="submit" name="delete" value="<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this slot?')">Remove</button>
                                        </form>
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
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
