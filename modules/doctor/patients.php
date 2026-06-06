<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('doctor');

require_once __DIR__ . '/../../config/database.php';
$userId = getCurrentUserId();

$stmt = $pdo->prepare("SELECT id FROM doctors WHERE user_id = ?");
$stmt->execute([$userId]);
$doctor = $stmt->fetch();
if (!$doctor) { die('Doctor profile not found.'); }

$stmt = $pdo->prepare("
    SELECT DISTINCT p.*, u.email,
        (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id AND doctor_id = ?) AS visit_count
    FROM appointments a
    JOIN patients p ON a.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE a.doctor_id = ?
    ORDER BY p.last_name ASC
");
$stmt->execute([$doctor['id'], $doctor['id']]);
$patients = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-people"></i> My Patients</h3>
    <a href="dashboard.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($patients)): ?>
            <p class="text-muted text-center mb-0">No patient records yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Visits</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                            <td><?= htmlspecialchars($p['contact'] ?? 'N/A') ?></td>
                            <td><?= $p['visit_count'] ?></td>
                            <td>
                                <a href="record.php?patient_id=<?= $p['id'] ?>" class="btn btn-sm btn-primary"><i class="bi bi-file-medical"></i> Medical Records</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
