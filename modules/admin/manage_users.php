<?php
require_once __DIR__ . '/../../includes/auth.php';
requireRole('admin');

require_once __DIR__ . '/../../config/database.php';

$stmt = $pdo->query("
    SELECT u.id, u.username, u.email, u.first_name, u.last_name, u.role, u.created_at
    FROM users u
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $id = (int)$_POST['delete_user'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    $stmt->execute([$id]);
    header('Location: manage_users.php');
    exit;
}
?>
<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4><i class="bi bi-people"></i> Manage Users</h4>
    <div>
        <a href="add_doctor.php" class="btn btn-success"><i class="bi bi-person-plus"></i> Add Doctor</a>
        <a href="add_staff.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Add Staff</a>
    </div>
</div>

<div class="card shadow">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= htmlspecialchars($u['first_name'] . ' ' . $u['last_name']) ?: 'N/A' ?></td>
                        <td><?= htmlspecialchars($u['username']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : ($u['role'] === 'doctor' ? 'primary' : ($u['role'] === 'secretary' ? 'info' : 'secondary')) ?>"><?= ucfirst($u['role']) ?></span></td>
                        <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin'): ?>
                                <form method="POST" style="display:inline">
                                    <button type="submit" name="delete_user" value="<?= $u['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user permanently?')">Delete</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted">--</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
