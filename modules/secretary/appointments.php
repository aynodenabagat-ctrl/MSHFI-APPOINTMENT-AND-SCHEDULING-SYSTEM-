<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['secretary', 'admin']);

require_once __DIR__ . '/../../config/database.php';

$csrfToken = generateCsrfToken();
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT a.*, p.first_name AS p_first, p.last_name AS p_last, d.first_name AS d_first, d.last_name AS d_last
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        JOIN doctors d ON a.doctor_id = d.id
        WHERE 1=1";
$params = [];

if ($statusFilter) {
    $sql .= " AND a.status = ?";
    $params[] = $statusFilter;
}
if ($search) {
    $sql .= " AND (CONCAT(p.first_name, ' ', p.last_name) LIKE ? OR CONCAT(d.first_name, ' ', d.last_name) LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY a.appointment_date DESC, a.appointment_time DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$appointments = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-calendar-event"></i> All Appointments</h4>
    <a href="dashboard.php" class="btn btn-outline-primary"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-3">
    <div class="d-flex gap-1 flex-wrap align-items-center">
        <a href="appointments.php" class="btn <?= !$statusFilter ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
        <a href="appointments.php?status=pending" class="btn <?= $statusFilter === 'pending' ? 'btn-primary' : 'btn-outline-secondary' ?>">Pending</a>
        <a href="appointments.php?status=confirmed" class="btn <?= $statusFilter === 'confirmed' ? 'btn-primary' : 'btn-outline-secondary' ?>">Confirmed</a>
        <a href="appointments.php?status=completed" class="btn <?= $statusFilter === 'completed' ? 'btn-primary' : 'btn-outline-secondary' ?>">Completed</a>
        <a href="appointments.php?status=cancelled" class="btn <?= $statusFilter === 'cancelled' ? 'btn-primary' : 'btn-outline-secondary' ?>">Cancelled</a>
        <?php if ($search || $statusFilter): ?>
            <span class="text-muted small ms-2"><?= count($appointments) ?> result<?= count($appointments) !== 1 ? 's' : '' ?></span>
        <?php endif; ?>
    </div>
    <form method="GET" class="d-flex gap-2">
        <?php if ($statusFilter): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
        <?php endif; ?>
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search patient or doctor..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
            <?php if ($search): ?>
                <a href="appointments.php<?= $statusFilter ? '?status=' . urlencode($statusFilter) : '' ?>" class="btn btn-outline-secondary"><i class="bi bi-x"></i></a>
            <?php endif; ?>
        </div>
    </form>
</div>

<?php if (empty($appointments)): ?>
<div class="text-center py-5">
    <i class="bi bi-calendar-x text-muted display-6"></i>
    <p class="text-muted mt-2 mb-0">No appointments found.</p>
</div>
<?php endif; ?>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appt): ?>
                    <tr>
                        <td><?= htmlspecialchars($appt['p_first'] . ' ' . $appt['p_last']) ?></td>
                        <td>Dr. <?= htmlspecialchars($appt['d_first'] . ' ' . $appt['d_last']) ?></td>
                        <td><?= date('M d, Y', strtotime($appt['appointment_date'])) ?></td>
                        <td><?= date('h:i A', strtotime($appt['appointment_time'])) ?></td>
                        <td>
                            <span class="badge bg-<?= $appt['status'] === 'confirmed' ? 'primary' : ($appt['status'] === 'completed' ? 'success' : ($appt['status'] === 'cancelled' ? 'danger' : 'warning')) ?>">
                                <?= ucfirst($appt['status']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($appt['status'] === 'pending'): ?>
                                    <form method="POST" action="confirm.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check"></i> Confirm</button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($appt['status'] !== 'cancelled' && $appt['status'] !== 'completed'): ?>
                                    <form method="POST" action="cancel.php" style="display:inline">
                                        <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                        <input type="hidden" name="_csrf_token" value="<?= $csrfToken ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" data-confirm="Cancel this appointment?"><i class="bi bi-x"></i> Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
