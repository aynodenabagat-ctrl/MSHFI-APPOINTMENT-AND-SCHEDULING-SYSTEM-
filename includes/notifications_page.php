<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/notifications.php';

requireLogin();
$userId = getCurrentUserId();
$role = getCurrentUserRole();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    markNotificationsRead($pdo, $userId);
    header('Location: notifications.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="bi bi-bell"></i> Notifications</h3>
    <form method="POST">
        <button type="submit" name="mark_read" class="btn btn-outline-primary btn-sm"><i class="bi bi-check-all"></i> Mark All Read</button>
    </form>
</div>

<div class="card shadow">
    <div class="card-body">
        <?php if (empty($notifications)): ?>
            <p class="text-muted text-center mb-0"><i class="bi bi-bell-slash"></i> No notifications yet.</p>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($notifications as $n): ?>
                <div class="list-group-item list-group-item-action <?= !$n['is_read'] ? 'list-group-item-primary fw-bold' : '' ?>">
                    <div class="d-flex w-100 justify-content-between">
                        <p class="mb-1"><?= htmlspecialchars($n['message']) ?></p>
                        <small class="text-muted"><?= date('M d, Y h:i A', strtotime($n['created_at'])) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="mt-3">
            <a href="<?= BASE_URL ?>/modules/<?= $role ?>/dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
